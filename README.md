### Installing Module Via Composer
There are two ways to install your module via composer.  
#### Public Module
We highly encourage you to share your created modules with the OpenEMR community.  To ensure that other developers / users can install
your packages please register your module on [https://packagist.org/](https://packagist.org/).  Once your module has been registered
users can install your package by doing a `composer require "<namespace>/<your-package-name>`
#### Private Module
If your module is a private module you can still tell composer where to find your module by setting it up to use a private repository.
You can do it with the following command:
```
composer config repositories.repo-name vcs https://github.com/<organization or user name>/<repository name>
```
For example to install this module you can run the following
```
composer config repositories.repo-name vcs https://github.com/Vikci/module_pecker.git
```

At that point you can run the install command
```
composer require openemr/custom-module-woodpecker
```

### Installing Module via filesystem
If you copy your module into the installation directory you will need to copy your module's composer.json "psr-4" property into your OpenEMR's psr-4 settings.
You will also need to run a ```composer dump-autoload``` wherever your openemr composer.json file is located in order to get your namespace properties setup properly
to include your module.

### Activating Your Module
Install your module using either composer (recommended) or by placing your module in the *<openemr_installation_directory>//interface/modules/custom_modules/*.

Once your module is installed in OpenEMR custom_modules folder you can activate your module in OpenEMR by doing the following.

  1. Login to your OpenEMR installation as an administrator
  2. Go to your menu and select Modules -> Manage Modules
  3. Now click the *Install* button next your module name.
  4. Finally click the *Enable* button for your module.
