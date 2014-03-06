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

class Protocol extends Admin {

    /**
     * Get the protocol file names for the form.factory
     *
     * @return multitype:string
     */
    protected function getProtocolFileNamesForSelect()
    {
        $files = glob(FRAMEWORK_PATH."/logfile/crawler-*.log");
        natsort($files);

        $result = array();
        foreach ($files as $file) {
            $basename = basename($file);
            $date = substr($basename, strlen('crawler-'), strlen('0000-00-00'));
            $result[$date] = $basename;
        }

        return $result;
    }

    /**
     * Get a form to select a Crawler protocol
     *
     * @param string $date
     */
    protected function getProtocolSelectForm($date=null)
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('protocol', 'choice', array(
                'choices' => $this->getProtocolFileNamesForSelect(),
                'empty_value' => '- please select -',
                'expanded' => false,
                'required' => true,
                'data' => !empty($date) ? $date : date('Y-m-d'),
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

        // set today as default date
        $date = date('Y-m-d');

        $form = $this->getProtocolSelectForm($date);

        if ('POST' == $this->app['request']->getMethod()) {
            // the protocol select form was submitted
            $form->bind($this->app['request']);

            if ($form->isValid()) {
                // the form is valid
                $data = $form->getData();
                $date = $data['protocol'];
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(), self::ALERT_TYPE_DANGER);
            }
        }

        $protocol = array();
        $protocol_file = sprintf(FRAMEWORK_PATH.'/logfile/crawler-%s.log', $date);

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
            '@phpManufaktur/WebmasterTools/Template', 'admin/protocol.twig'),
            array(
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('protocol'),
                'form' => $form->createView(),
                'date' => $date,
                'protocol' => $protocol
            ));
    }

}
