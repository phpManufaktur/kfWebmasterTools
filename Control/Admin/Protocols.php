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

class Protocols extends Admin {

    /**
     * Get the protocol file names for the form.factory
     *
     * @return multitype:string
     */
    protected function getProtocolFileNamesForSelect()
    {
        $files = array();
        foreach (self::$config['protocol']['pattern'] as $pattern) {
            $log = glob(FRAMEWORK_PATH.$pattern);
            if (is_array($log)) {
                $files = array_merge($files, $log);
            }
        }

        $result = array();

        natsort($files);

        foreach ($files as $file) {
            $basename = basename($file);
            $date = substr($basename, strpos($basename, '-')+1, strlen('0000-00-00'));
            $result[$file] = $basename;
        }
        return $result;
    }

    /**
     * Get a form to select a Crawler protocol
     *
     * @param string $date
     */
    protected function getProtocolSelectForm($protocol_file=null)
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('protocol', 'choice', array(
                'choices' => $this->getProtocolFileNamesForSelect(),
                'empty_value' => '- please select -',
                'expanded' => false,
                'required' => true,
                'data' => !empty($protocol_file) ? $protocol_file : sprintf(FRAMEWORK_PATH.'/logfile/framework-%s.log', date('Y-m-d')),
            ))
            ->getForm();
    }

    /**
     * Controller
     *
     * @param Application $app
     * @return string
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        // preselect the general framework logfile of today
        $protocol_file = sprintf(FRAMEWORK_PATH.'/logfile/framework-%s.log', date('Y-m-d'));

        $form = $this->getProtocolSelectForm($protocol_file);

        if ('POST' == $this->app['request']->getMethod()) {
            // the protocol select form was submitted
            $form->bind($this->app['request']);

            if ($form->isValid()) {
                // the form is valid
                $data = $form->getData();
                //$date = $data['protocol'];
                $protocol_file = $data['protocol'];
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(), self::ALERT_TYPE_DANGER);
            }
        }
        else {
            $this->setAlert('This dialog enable you to view the kitFramework logfiles. By default the actual logfile will be shown.');
        }

        $protocol = array();

        if ($app['filesystem']->exists($protocol_file)) {
            // load only one line at a time
            if (false === ($handle = fopen($protocol_file, 'r'))) {
                $error = error_get_last();
                throw new \Exception($error['message']);
            }
            $lines = array();
            while (false !== ($line = fgets($handle))) {
                $lines[] = $line;
            }
            $protocol = array_reverse($lines);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/WebmasterTools/Template', 'admin/protocols.twig'),
            array(
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('protocol'),
                'form' => $form->createView(),
                'protocol' => $protocol
            ));
    }

}
