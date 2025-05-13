
## Getting Started
You can start by cloning the project.  When developing modules the best initial location would be to clone the directory
inside the OpenEMR custom modules location.  This is at *<openemr_installation_directory>//interface/modules/custom_modules/*
```git
git clone https://github.com/Vikci/module_pecker.git
```
### Installing Module Via Composer
There are two ways to install your module via composer.  
#### Public Module
We highly encourage you to share your created modules with the OpenEMR community.  To ensure that other developers / users can install
your packages please register your module on [https://packagist.org/](https://packagist.org/).  Once your module has been registered
users can install your package by doing a `composer require "<namespace>/<your-package-name>`

  1. Login to your OpenEMR installation as an administrator
  2. Go to your menu and select Modules -> Manage Modules
  3. Click on the Unregistered tab in your modules list
  4. Find your module and click the *Register* button.  This will reload the page and put your module in the Registered list tab of your modules
  5. Now click the *Install* button next your module name.
  6. Finally click the *Enable* button for your module.

## Contributing
If you would like to help in improving the skeleton library just post an issue on Github or send a pull request.