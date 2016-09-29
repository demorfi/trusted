# trusted - Dockerized SSL certificates manager

This simple dockerized SSL certificates manager includes the following features:
* Individual root CA setup with password protected private key to sign certificate sign requests (CSR)
* Manage users and their permissions based on domains
* Create SSL certificates with private key
* Intuitive and simple to use GUI based on bootstrap
* Built on Laravel 4 and passion in Berlin & Munich


## Security Advice

Please be advised that the created Root Certificate Authority key file must be protected by all means. Choose a strong password and don't store it together with the keyfile. Also note that all other key files created by this application are *not* password protected for convenient use in a server environment.

## Setup using  [Docker](http://docker.io)

The container by default exposes port 80 and suggests the folder `/data` to be mapped as a volume.

*Caution: If you don't setup a volume mapping to `/data`, your newly created certificates and the backend database will be lost upon destroying the container.*

### Example `docker run`
The following command will download and run the image, mapping the local port 8000 to the containers port 80 and a folder called `trusted` in your home directory to the containers `/data` folder.

    docker run --restart=always -d -p 8000:80/tcp -v ~/trusted:/data tfohlmeister/trusted2:latest

Now open up `localhost:8000` in your browser follow the instructions under Usage.

## Usage

After the setup open the app in a browser of your choice. You will be asked for username and password. Initial credentials are admin / password.

You will have to create a root CA first. Afterwards create users and certificates. You can determine different domains of a user by separating them with commas.

Enjoy and contribute!


## License

Package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
