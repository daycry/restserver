<?php

namespace Daycry\RestServer\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use DateTime;

class DiscoverClasses extends BaseCommand
{
    protected $group       = 'Rest Server';
    protected $name        = 'restserver:discover';
    protected $description = 'Discover classes from namespace to import in database.';

    protected Datetime $timeStart;
    protected BaseConfig $config;
    protected array $allClasses = [];

    public function run(array $params)
    {
        $this->timeStart = ( new DateTime('now'))->modify('-1 second');

        $this->config = config('RestServer');

        if (!empty($this->config->restNamespaceScope)) {
            $finderConfig = config('ClassFinder');
            $finderConfig->finder['files'] = false;

            $api = $this->_checkApiModel();

            foreach ($this->config->restNamespaceScope as $namespace) {
                //remove "\" for search in class-finder
                $namespace = (mb_substr($namespace, 0, 1) == '\\') ? mb_substr($namespace, 1) : $namespace;

                $classes = (new \Daycry\ClassFinder\ClassFinder($finderConfig))->getClassesInNamespace($namespace);
                if ($classes) {
                    foreach ($classes as $class) {
                        \array_push($this->allClasses, '\\' . $class);

                        $methods = $this->_getMethodsFromCLass($class);

                        foreach ($methods as $key => $value) {
                            if ($value[0] == '_') {
                                unset($methods[$key]);
                            }
                        }

                        $class = (mb_substr($class, 0, 1) !== '\\') ? '\\' . $class : $class;

                        $this->_checkClassNamespace($api, $class, $methods);
                    }

                    unset($classes);
                }
            }

            //removing obsolet namespaces
            $namespaceModel = new \Daycry\RestServer\Models\NamespaceModel();
            $namespaces = ($api->{$this->config->restNamespaceTable}) ? $api->{$this->config->restNamespaceTable} : [];
            foreach ($namespaces as $n) {
                // @codeCoverageIgnoreStart
                if (!$n instanceof \Daycry\RestServer\Entities\NamespaceEntity) {
                    $n = new \Daycry\RestServer\Entities\NamespaceEntity((array)$n);
                }
                // @codeCoverageIgnoreEnd

                if (!\in_array($n->controller, $this->allClasses)) {
                    $requestModel = new \Daycry\RestServer\Models\PetitionModel();
                    $availableRequest = ($n->{$this->config->configRestPetitionsTable}) ? $n->{$this->config->configRestPetitionsTable} : [];

                    foreach ($availableRequest as $r) {
                        $requestModel->delete($r->id);
                    }
                    $namespaceModel->delete($n->id);
                }
            }
        }

        CLI::write('**** FINISHED. ****', 'white', 'green');
    }

    private function _checkApiModel(): ?\Daycry\RestServer\Entities\ApiEntity
    {
        $apiModel = new \Daycry\RestServer\Models\ApiModel();
        $api = $apiModel->where('url', site_url())->first();

        if (!$api) {
            $api = new \Daycry\RestServer\Entities\ApiEntity();
            $api->fill(array('url' => site_url()));
            $apiModel->save($api);
            $api->id = $apiModel->getInsertID();
        } else {
            $api->fill(array( 'checked_at' => (new DateTime('now'))->format('Y-m-d H:i:s') ));
            $apiModel->save($api);
        }

        return $api;
    }

    private function _getMethodsFromCLass($namespace): array
    {
        $f = new \ReflectionClass($namespace);
        $methods = array();
        foreach ($f->getMethods() as $m) {
            if ($m->class == $namespace) {
                $methods[] = $m->name;
            }
        }

        return $methods;
    }

    private function _checkClassNamespace(\Daycry\RestServer\Entities\ApiEntity $api, string $class, array $methods = [])
    {
        $namespaceModel = new \Daycry\RestServer\Models\NamespaceModel();

        $found = false;
        $namespaces = $api->{$this->config->restNamespaceTable};
        if ($namespaces) {
            foreach ($namespaces as $namespace) {
                // @codeCoverageIgnoreStart
                if (!$namespace instanceof \Daycry\RestServer\Entities\NamespaceEntity) {
                    $namespace = new \Daycry\RestServer\Entities\NamespaceEntity((array)$namespace);
                }
                // @codeCoverageIgnoreEnd

                if ($namespace->controller == $class) {
                    $namespace->fill(array( 'checked_at' => (new DateTime('now'))->format('Y-m-d H:i:s'), 'methods' => $methods) );
                    $namespaceModel->save($namespace);
                    $found = true;
                    break;
                }
            }
            unset($namespaces);
        }

        if (!$found) {
            $namespace = new \Daycry\RestServer\Entities\NamespaceEntity();
            $namespace = $namespace->setController($class);
            $namespace->methods = $methods;
            $namespace->api_id = $api->id;
            $namespaceModel->save($namespace);
        } else {
            $availableRequest = ($namespace->{$this->config->configRestPetitionsTable}) ? $namespace->{$this->config->configRestPetitionsTable} : [];
            $requestModel = new \Daycry\RestServer\Models\PetitionModel();

            foreach ($availableRequest as $request) {
                if ($request->method === null || \in_array($request->method, $methods)) {
                    $request->checked_at = (new DateTime('now'))->format('Y-m-d H:i:s');
                    $requestModel->save($request);
                } else {
                    $requestModel->delete($request->id);
                }
            }
        }
    }
}
