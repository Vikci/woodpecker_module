<?php

/**
 * Bootstrap for the CustomModuleWpecker module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\CustomModuleWpecker;

/**
 * Note the below use statements are importing classes from the OpenEMR core codebase
 */
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Events\RestApiExtend\RestApiResourceServiceEvent;
use OpenEMR\Events\RestApiExtend\RestApiScopeEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

// we import our own classes here.. although this use statement is unnecessary it forces the autoloader to be tested.
use OpenEMR\Modules\CustomModuleWpecker\CustomSkeletonRestController;


class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    const MODULE_NAME = "custom-module-woodpecker";
    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private $eventDispatcher;

    /**
     * @var GlobalConfig Holds our module global configuration values that can be used throughout the module.
     */
    private $globalsConfig;

    /**
     * @var string The folder name of the module.  Set dynamically from searching the filesystem.
     */
    private $moduleDirectoryName;

    /**
     * @var \Twig\Environment The twig rendering environment
     */
    private $twig;

    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, ?Kernel $kernel = null)
    {
        global $GLOBALS;

        if (empty($kernel)) {
            $kernel = new Kernel();
        }

        // NOTE: eventually you will be able to pull the twig container directly from the kernel instead of instantiating
        // it here.
        $twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;

        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->eventDispatcher = $eventDispatcher;

        // we inject our globals value.
        $this->globalsConfig = new GlobalConfig($GLOBALS);
        $this->logger = new SystemLogger();
    }

    public function subscribeToEvents()
    {
        $this->addGlobalSettings();

        // we only add the rest of our event listeners and configuration if we have been fully setup and configured
        if ($this->globalsConfig->isConfigured()) {
            $this->registerMenuItems();
            $this->registerTemplateEvents();
            $this->subscribeToApiEvents();
        }
    }

    /**
     * @return GlobalConfig
     */
    public function getGlobalConfig()
    {
        return $this->globalsConfig;
    }

    public function addGlobalSettings()
    {
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalSettingsSection']);
    }

    public function addGlobalSettingsSection(GlobalsInitializedEvent $event)
    {
        global $GLOBALS;

        $service = $event->getGlobalsService();
        $section = xlt("Woodpecker Module");
        $service->createSection($section, 'Portal');

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $value = $GLOBALS[$key] ?? $config['default'];
            $service->appendToSection(
                $section,
                $key,
                new GlobalSetting(
                    xlt($config['title']),
                    $config['type'],
                    $value,
                    xlt($config['description']),
                    true
                )
            );
        }
    }

    /**
     * We tie into any events dealing with the templates / page rendering of the system here
     */
    public function registerTemplateEvents()
    {
        if ($this->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_ENABLE_BODY_FOOTER)) {
            $this->eventDispatcher->addListener(RenderEvent::EVENT_BODY_RENDER_POST, [$this, 'renderMainBodyScripts']);
        }
    }

    /**
     * Add our javascript and css file for the module to the main tabs page of the system
     * @param RenderEvent $event
     */
    public function renderMainBodyScripts(RenderEvent $event)
    {
        ?>
        <link rel="stylesheet" href="<?php echo $this->getAssetPath();?>css/skeleton-module.css">
        <script src="<?php echo $this->getAssetPath();?>js/skeleton-module.js"></script>
        <?php
    }

    /**
     * @param TwigEnvironmentEvent $event
     */
    public function addTemplateOverrideLoader(TwigEnvironmentEvent $event)
    {
        try {
            $twig = $event->getTwigEnvironment();
            if ($twig === $this->twig) {
                // we do nothing if its our own twig environment instantiated that we already setup
                return;
            }
            // we make sure we can override our file system directory here.
            $loader = $twig->getLoader();
            if ($loader instanceof FilesystemLoader) {
                $loader->prependPath($this->getTemplatePath());
            }
        } catch (LoaderError $error) {
            $this->logger->errorLogCaller("Failed to create template loader", ['innerMessage' => $error->getMessage(), 'trace' => $error->getTraceAsString()]);
        }
    }

    public function registerMenuItems()
    {
        if ($this->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_ENABLE_MENU)) {
            /**
             * @var EventDispatcherInterface $eventDispatcher
             * @var array $module
             * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
             * @global                       $module @see ModulesApplication::loadCustomModule
             */
            $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addCustomModuleMenuItem']);
        }
    }

    public function addCustomModuleMenuItem(MenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'mod0';
        $menuItem->label = xlt("Custom Module Skeleton");
        // TODO: pull the install location into a constant into the codebase so if OpenEMR changes this location it
        // doesn't break any modules.
        $menuItem->url = "/interface/modules/custom_modules/woodpecker_module/public/sample-index.php";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Several examples are provided
         */
        $menuItem->acl_req = [];

        /**
         * If you would like to restrict this menu to only logged in users who have access to see all user data
         */
        //$menuItem->acl_req = ["admin", "users"];

        /**
         * If you would like to restrict this menu to logged in users who can access patient demographic information
         */
        //$menuItem->acl_req = ["users", "demo"];


        /**
         * This menu flag takes a boolean property defined in the $GLOBALS array that OpenEMR populates.
         * It allows a menu item to display if the property is true, and be hidden if the property is false
         */
        //$menuItem->global_req = ["custom_skeleton_module_enable"];

        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'modimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }

    public function subscribeToApiEvents()
    {
        if ($this->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_ENABLE_FHIR_API)) {
            $this->eventDispatcher->addListener(RestApiCreateEvent::EVENT_HANDLE, [$this, 'addCustomSkeletonApi']);
            $this->eventDispatcher->addListener(RestApiScopeEvent::EVENT_TYPE_GET_SUPPORTED_SCOPES, [$this, 'addApiScope']);
            $this->eventDispatcher->addListener(RestApiResourceServiceEvent::EVENT_HANDLE, [$this, 'addMetadataConformance']);
        }
    }

    public function addCustomSkeletonApi(RestApiCreateEvent $event)
    {
        $apiController = new CustomSkeletonRestController();

        /**
         * To see the route definitions @see https://github.com/openemr/openemr/blob/master/_rest_routes.inc.php
         */
        $event->addToFHIRRouteMap('GET /fhir/CustomSkeletonResource', [$apiController, 'listResources']);
        $event->addToFHIRRouteMap('GET /fhir/CustomSkeletonResource/:fhirId', [$apiController, 'getOneResource']);

        /**
         * Events must ALWAYS be returned
         */
        return $event;
    }

    /**
     * Adds the webhook api scopes to the oauth2 scope validation events for the standard api.  This allows the webhook
     * to be fired.
     * @param RestApiScopeEvent $event
     * @return RestApiScopeEvent
     */
    public function addApiScope(RestApiScopeEvent $event)
    {
        if ($event->getApiType() == RestApiScopeEvent::API_TYPE_FHIR) {
            $scopes = $event->getScopes();
            $scopes[] = 'user/CustomSkeletonResource.read';
            $scopes[] = 'patient/CustomSkeletonResource.read';
            // only add system scopes if they are actually enabled
            if (\RestConfig::areSystemScopesEnabled())
            {
                $scopes[] = 'system/CustomSkeletonResource.read';
            }
            $event->setScopes($scopes);
        }
        return $event;
    }

    public function addMetadataConformance(RestApiResourceServiceEvent $event)
    {
        $event->setServiceClass(CustomSkeletonFHIRResourceService::class);
        return $event;
    }

    private function getPublicPath()
    {
        return self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    private function getAssetPath()
    {
        return $this->getPublicPath() . 'assets' . DIRECTORY_SEPARATOR;
    }

    public function getTemplatePath()
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }
}
