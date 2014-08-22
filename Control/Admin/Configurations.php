<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\WebmasterTools\Control\Admin;

use phpManufaktur\WebmasterTools\Control\Admin\Admin;
use Silex\Application;

class Configurations extends Admin
{

    /**
     * Get all available configuration files for the form.factory select
     *
     * @return multitype:string
     */
    protected function getConfigurationFiles(&$information=array())
    {
        $files = array();

        // get all kitFramework configuration files
        foreach (self::$config['configuration']['framework']['pattern'] as $pattern) {
            $config_files = glob(FRAMEWORK_PATH.'/config'.$pattern);
            if (is_array($config_files)) {
                $files = array_merge($files, $config_files);
            }
        }

        $extension_directories = array(
            '/extension/phpmanufaktur/phpManufaktur',
            '/extension/thirdparty/thirdParty'
        );

        // loop through the extensions and gather all configuration files
        foreach ($extension_directories as $extension_directory) {
            $directories = glob(FRAMEWORK_PATH.$extension_directory.'/*' , GLOB_ONLYDIR);
            foreach ($directories as $directory) {
                foreach (self::$config['configuration']['extension']['pattern'] as $pattern) {
                    $config_files = glob($directory.$pattern);
                    if (is_array($config_files)) {
                        $files = array_merge($files, $config_files);
                    }
                }
            }
        }

        $result = array();
        $information = array();

        foreach ($files as $file) {
            $config_file = basename($file);
            $result[$file] = $config_file;
            if (isset(self::$config['configuration']['information'][$config_file])) {
                // set additional information about the configuration file
                $extension = $this->app['utils']->readJSON(FRAMEWORK_PATH.self::$config['configuration']['information'][$config_file]['path']);
                $information[$file] = array(
                    'file' => $config_file,
                    'image' => FRAMEWORK_URL.self::$config['configuration']['information'][$config_file]['image'],
                    'release' => $extension['release'],
                    'wiki' => self::$config['configuration']['information'][$config_file]['wiki']
                    );
                self::$config['configuration']['information'][$config_file];
            }
        }

        // sort the config files
        asort($result);
        return $result;
    }

    /**
     * Get a form to select a configuration file
     *
     * @param string $date
     */
    protected function getConfigurationSelectForm($configuration_file=null, &$information=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('config_file', 'choice', array(
            'choices' => $this->getConfigurationFiles($information),
            'empty_value' => '- please select -',
            'expanded' => false,
            'required' => true,
            'data' => !empty($configuration_file) ? $configuration_file : null
        ))
        ->getForm();
    }

    /**
     * Controller to save the actual configuration JSON file.
     * Will be called as Ajax response by configurations.twig
     *
     * @param Application $app
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ControllerSaveJSON(Application $app)
    {
        try {
            $this->initialize($app);

            if (null === ($file = $app['request']->request->get('file'))) {
                $error = 'Missing the POST parameter for the JSON filename!';
                $app['monolog']->addError($error, array(__METHOD__, __LINE__));
                return $app->json(array(
                    'alert' => 'alert-danger',
                    'result' => sprintf('[%s - %s] %s', __METHOD__, __LINE__, $app['translator']->trans($error))
                    ));
            }
            if (null === ($data = $app['request']->request->get('data'))) {
                $error = 'Missing the POST parameter for the JSON data!';
                $app['monolog']->addError($error, array(__METHOD__, __LINE__));
                return $app->json(array(
                    'alert' => 'alert-danger',
                    'result' => sprintf('[%s - %s] %s', __METHOD__, __LINE__, $app['translator']->trans($error))
                ));
            }
            $json = json_decode($data, true);
            if (false === file_put_contents($file, $app['utils']->JSONFormat($json))) {
                $error = error_get_last();
                $app['monolog']->addError($error['message'], array($error['file'], $error['line']));
                return $app->json(array(
                    'alert' => 'alert-danger',
                    'result' => sprintf('[%s - %s] %s', basename($error['file']), $error['line'], $error['message'])
                ));
            }

            return $app->json(array(
                'alert' => 'alert-success',
                'result' => $app['translator']->trans('Successfull saved the configuration file %file%.',
                    array('%file%' => basename($file)))));
        }
        catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Default controller to select JSON configuration files and the JSON editor
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $information = array();
        $form = $this->getConfigurationSelectForm(null, $information);

        $json_file = null;
        $json = null;

        if ('POST' == $this->app['request']->getMethod()) {
            // the configuration select form was submitted
            $form->bind($this->app['request']);

            if ($form->isValid()) {
                // the form is valid
                $data = $form->getData();
                //$date = $data['protocol'];
                $json_file = $data['config_file'];
                $json = file_get_contents($json_file);
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(), self::ALERT_TYPE_DANGER);
            }
        }
        else {
            // give a hint for the usage
            $this->setAlert('This dialog enable you to edit all configuration files of the kitFramework itself and all installed extensions of the phpManufaktur and of third party manufacturers. Please select the configuration file you want to edit.',
                array(), self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/WebmasterTools/Template', 'admin/configurations.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('configuration'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'json' => array(
                    'data' => $json,
                    'file' => $json_file
                ),
                'form' => $form->createView(),
                'information' => $information
            ));
    }
}
