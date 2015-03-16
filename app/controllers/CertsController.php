<?php

use Symfony\Component\Process\Process;

class CertsController extends \BaseController {
	private $certDir;

	public function __construct() {
		$this->certDir = base_path() . '/certs/';
		$this->beforeFilter('rootCAExists', ['except' => ['rootCAIndex', 'rootCACreate']]);
	}

	public function redirect() {
		return Redirect::route('certs-path');
	}

	public function index() {
		if(Auth::user()->isAdmin())
			$certs = Cert::with('owner')->orderBy('domain', 'ASC')->get();
		else
			$certs = Auth::user()->certs;

		return View::make('certs')
			->withCerts($certs);
	}

	public function store() {
		$input = Input::all();
		$rules = [
			'c' => 'required',
			'cn' => 'required',
			'cns' => 'required',
			'root_password' => 'required',
		];

		$v = Validator::make($input, $rules);

		if($v->fails())
			return Redirect::route('certs-path')
				->withInput(Input::except('root_password'))
				->with('error', 'Please provide at least the country, a common name and the password of your Root CA.');

		if(!in_array('*'.$input['cns'], Auth::user()->domains))
			return Redirect::route('certs-path')
				->withInput(Input::except('root_password'))
				->with('error', 'You are not allowed to create a certificate for this domain.');

		$sluggedDomain = Str::slug(str_replace('.', '_', $input['cn'].$input['cns']));
		if(!File::exists($this->certDir . $sluggedDomain . '.crt')) {
			// Get field
			$c = Input::get('c');
			$st = Input::get('st');
			$l = Input::get('l');
			$o = Input::get('o');
			$ou = Input::get('ou');
			$cn = $input['cn'].$input['cns'];
			$email = Input::get('email');
			$root_pw = Input::get('root_password');

			// Prepare subject
			$subj = "'/C={$c}";
			$subj .= !empty($st) ? "/ST={$st}" : '';
			$subj .= !empty($l) ? "/L={$l}" : '';
			$subj .= !empty($o) ? "/O={$o}" : '';
			$subj .= !empty($ou) ? "/OU={$ou}" : '';
			$subj .= "/CN={$cn}";
			$subj .= !empty($email) ? "/Email={$email}" : '';
			$subj .= "'";

			// Create private key and CSR
			$process = new Process("cd {$this->certDir} && openssl req -nodes -new -newkey rsa:2048 -sha256 -keyout {$sluggedDomain}.key -out {$sluggedDomain}.csr -days 365 -subj {$subj}");
			$process->run();

			if (!$process->isSuccessful()) {
				File::delete($this->certDir.$sluggedDomain.'.csr'); // cleanup
				File::delete($this->certDir.$sluggedDomain.'.pem');
				File::delete($this->certDir.$sluggedDomain.'.key');
				return Redirect::route('certs-path')
					->withInput(Input::except('root_password'))
					->with('error', 'Could not create private key or CSR. '.$process->getErrorOutput());
			}

			// Sign cert, convert into DES and remove CSR
			$process = new Process("cd {$this->certDir} && openssl x509 -passin pass:{$root_pw} -CA rootCA.pem -CAkey rootCA.key -CAcreateserial -days 365 -req -in {$sluggedDomain}.csr -out {$sluggedDomain}.pem && openssl x509 -in {$sluggedDomain}.pem -out {$sluggedDomain}.crt");
			$process->run();

			if (!$process->isSuccessful()) {
				File::delete($this->certDir.$sluggedDomain.'.csr'); // cleanup
				File::delete($this->certDir.$sluggedDomain.'.pem');
				File::delete($this->certDir.$sluggedDomain.'.key');
				return Redirect::route('certs-path')
					->withInput(Input::except('root_password'))
					->with('error', 'Could not sign certificate. Is the provided Root CA password correct? '.nl2br($process->getErrorOutput()));
			}

			File::delete($this->certDir.$sluggedDomain.'.csr');

			// Create database entry
			Cert::create([
				'user_id' => Auth::user()->id,
				'domain' => $cn,
				'csr' => 0,
			]);

			return Redirect::route('certs-path')
				->with('success', 'Certificate has been created.');
		} else {
			return Redirect::route('certs-path')
				->withInput(Input::except('root_password'))
				->with('warning', 'Certificate already exists.');
		}

		
	}

	public function sign() {
		$input = Input::all();
		$rules = [
			'csr' => 'required',
			'root_password' => 'required',
		];

		$v = Validator::make($input, $rules);

		if($v->fails())
			return Redirect::route('certs-path')
				->withInput(Input::except('root_password'))
				->with('error', 'Please select a CSR and provide the Root CA password.');

		// Upload CSR
		$randomFileName = str_random();
		Input::file('csr')->move($this->certDir, $randomFileName.'.csr');

		// Check if CSR is valid and user is allowed to sign requests for the given domain
		$process = new Process("cd {$this->certDir} && LC_ALL=C openssl req -text -noout -in {$randomFileName}.csr | grep Subject | grep -o 'CN=.*,' | cut -c 4- | sed 's/.$//'");
		$process->run();
		$csrDomain = trim($process->getOutput());

		// Calculate domain string to check against users allowed domain pool
		$checkDomain = '*'.substr($csrDomain, strpos($csrDomain, '.'));

		if(!$csrDomain || !in_array($checkDomain, Auth::user()->domains)) {
			File::delete($this->certDir.$sluggedDomain.'.csr'); // cleanup
			return Redirect::route('certs-path')
				->withInput(Input::except('root_password'))
				->with('error', 'The provided CSR file is invalid or you are not allowed to create a certificate for the given domain.');
		}

		// Sign CSR
		$sluggedDomain = Str::slug(str_replace('.', '_', $csrDomain));
		$process = new Process("cd {$this->certDir} && openssl x509 -passin pass:{$root_pw} -CA rootCA.pem -CAkey rootCA.key -CAcreateserial -days 365 -req -in {$randomFileName}.csr -out {$randomFileName}.pem && openssl x509 -in {$randomFileName}.pem -out {$sluggedDomain}.crt");
		$process->run();

		// Remove CSR and PEM
		File::delete($this->certDir.$randomFileName.'.csr');
		File::delete($this->certDir.$randomFileName.'.pem');

		if (!$process->isSuccessful()) {
			return Redirect::route('certs-path')
				->withInput(Input::except('root_password'))
				->with('error', 'Could not sign certificate. Is the provided Root CA password correct? '.nl2br($process->getErrorOutput()));
		}
		

		// Create database entry
		Cert::create([
			'user_id' => Auth::user()->id,
			'domain' => $csrDomain,
			'csr' => 1,
		]);

		// Return signed certificate as download
		return Response::download($this->certDir.$sluggedDomain.'.crt');
	}

	// Todo: Put the following 4 methods into singleton and assign $sluggedDomain in constructor
	public function getCert($certId){
		$cert = Cert::find($certId);

		if(!$cert || ($cert->owner->id != Auth::user()->id && !Auth::user()->isAdmin()))
			return '';

		$sluggedDomain = Str::slug(str_replace('.', '_', $cert->domain));

		return nl2br(File::get($this->certDir.$sluggedDomain.'.crt'));
	}

	public function downloadCert($certId){
		$cert = Cert::find($certId);

		if(!$cert || ($cert->owner->id != Auth::user()->id && !Auth::user()->isAdmin()))
			return Redirect::route('certs-path');

		$sluggedDomain = Str::slug(str_replace('.', '_', $cert->domain));

		return Response::download($this->certDir.$sluggedDomain.'.crt');
	}

	public function getKey($certId){
		$cert = Cert::find($certId);

		if(!$cert || ($cert->owner->id != Auth::user()->id && !Auth::user()->isAdmin()))
			return '';

		$sluggedDomain = Str::slug(str_replace('.', '_', $cert->domain));

		return nl2br(File::get($this->certDir.$sluggedDomain.'.key'));
	}

	public function downloadKey($certId){
		$cert = Cert::find($certId);

		if(!$cert || ($cert->owner->id != Auth::user()->id && !Auth::user()->isAdmin()))
			return Redirect::route('certs-path');

		$sluggedDomain = Str::slug(str_replace('.', '_', $cert->domain));

		return Response::download($this->certDir.$sluggedDomain.'.key');
	}

	public function destroy($certId) {
		$cert = Cert::find($certId);

		if(!$cert || ($cert->owner->id != Auth::user()->id && !Auth::user()->isAdmin()))
			return Redirect::route('certs-path');

		$sluggedDomain = Str::slug(str_replace('.', '_', $cert->domain));
		$cert->delete();

		File::delete($this->certDir.$sluggedDomain.'.key');
		File::delete($this->certDir.$sluggedDomain.'.pem');
		File::delete($this->certDir.$sluggedDomain.'.crt');

		return Redirect::route('certs-path');
	}

	public function rootCAIndex() {
		$rootCACertExists = File::exists($this->certDir . 'rootCA.crt');

		if(!$rootCACertExists)
			return View::make('rootCACreate');

		// Get certificate information
		$process = new Process("cd {$this->certDir} && openssl x509 -in rootCA.crt -text -noout");
		$process->run();
		$certInfo = $process->getOutput();

		return View::make('rootCA')
			->withCert($certInfo);
	}

	public function rootCACreate() {
		$input = Input::all();
		$rules = [
			'c' => 'required',
			'cn' => 'required',
			'email' => 'required|email',
			'password1' => 'required',
			'password2' => 'required'
		];

		$v = Validator::make($input, $rules);

		if($v->fails())
			return Redirect::route('root-ca-path')
				->withInput(Input::except('password1'),Input::except('password2'))
				->with('error', 'Please provide at least country, common name, an email address and a password to protect the keyfile.');

		if(Input::get('password1')!==Input::get('password2'))
			return Redirect::route('root-ca-path')
				->withInput(Input::except('password1'),Input::except('password2'))
				->with('error', 'The provided passwords do not match, please try again.');

		if(!File::exists($this->certDir . 'rootCA.crt')) {
			$c = Input::get('c');
			$st = Input::get('st');
			$l = Input::get('l');
			$o = Input::get('o');
			$ou = Input::get('ou');
			$cn = Input::get('cn');
			$email = Input::get('email');
			$pw = Input::get('password1');

			$process = new Process("cd {$this->certDir} && openssl req -new -newkey rsa:4096 -sha256 -x509 -passout pass:{$pw} -keyout rootCA.key -out rootCA.pem -days 3650 -subj '/C={$c}/ST={$st}/L={$l}/O={$o}/OU={$ou}/CN={$cn}/Email={$email}'");
			$process->run();

			if (!$process->isSuccessful()) {
				return Redirect::route('root-ca-path')
					->withInput(Input::except('password1'),Input::except('password2'))
					->with('error', 'Could not create keyfile. '.$process->getErrorOutput());
			}

			$process = new Process("cd {$this->certDir} && openssl x509 -passin pass:{$pw} -in rootCA.pem -out rootCA.crt");
			$process->run();

			if (!$process->isSuccessful()) {
				return Redirect::route('root-ca-path')
					->withInput(Input::except('password1'),Input::except('password2'))
					->with('error', 'Could not create certificate. '.nl2br($process->getErrorOutput()));
			}
		}

		return Redirect::route('root-ca-path')
			->with('success', 'Root CA has been created. Be sure to remember its password!');
	}

	public function rootCADownloadCert() {
		return Response::download($this->certDir . 'rootCA.crt');
	}

	public function rootCADownloadKey() {
		return Response::download($this->certDir . 'rootCA.key');
	}

	public function rootCARemove() {
		File::delete($this->certDir . 'rootCA.crt');
		File::delete($this->certDir . 'rootCA.key');
		File::delete($this->certDir . 'rootCA.pem');
		File::delete($this->certDir . 'rootCA.srl');

		return Redirect::route('root-ca-path')
			->with('success', 'Root CA has been deleted.');
	}

}
