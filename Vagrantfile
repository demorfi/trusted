# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "puphpet/ubuntu1404-x64"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  
  config.vm.provider "virtualbox" do |vb|
    vb.name = "Trusted SSL Certificates Manager"
    vb.gui = false
    vb.memory = "1024"
  end

  config.vm.provider "parallels" do |v|
    v.name = "Trusted SSL Certificates Manager"

    # Uncomment the following line if you have problems with the port forward
    # v.update_guest_tools = true
    v.memory = "1024"
  end

  # Install software
  config.vm.provision "shell", inline: <<-SHELL
    sudo apt-get update
    sudo apt-get install -y vim apache2 php5 php5-sqlite php5-cli php5-mcrypt openssl
    sudo php5enmod mcrypt
  SHELL

  # Install dependencies
  config.vm.provision "shell", inline: <<-SHELL
    cd /vagrant
    php -r "readfile('https://getcomposer.org/installer');" | php
    php composer.phar install
    php composer.phar update
    php artisan trusted:setup
  SHELL

  # Setup Apache
  config.vm.provision "shell", inline: <<-SHELL
    sudo a2enmod rewrite
    if ! [ -L /var/www ]; then
      sudo rm -rf /var/www
      sudo ln -fs /vagrant/public /var/www
    fi
    sudo cp /vagrant/_provisioning/vhost.conf /etc/apache2/sites-available/000-default.conf
    sudo service apache2 restart
  SHELL

  # Set the Timezone to host timezone 
  # Source: https://coderwall.com/p/v8fr2g/a-quick-hack-to-set-the-timezone-for-a-vagrant-vm-in-ubuntu
  require 'time'
  offset = ((Time.zone_offset(Time.now.zone)/60)/60)
  zone_sufix = offset >= 0 ? "+#{offset.to_s}" : "#{offset.to_s}"
  timezone = 'Etc/GMT' + zone_sufix
  config.vm.provision :shell, :inline => "echo \"#{timezone}\" | sudo tee /etc/timezone && sudo dpkg-reconfigure --frontend noninteractive tzdata"
  
end
