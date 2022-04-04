<?php

if (!function_exists('restserver_instance')) {
    /**
     * load twig
     *
     * @return class
     */
    function restserver_instance()
    {
        return \CodeIgniter\Config\Services::restserver();
    }
}
