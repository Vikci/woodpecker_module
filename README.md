### Installing Module Via Composer
Install the package by doing a `composer require vikci/woodpecker_module`

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
