<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Service\Request;

abstract class FinderHandler
{
    protected $successor;
    protected $found;
    
    final public function append(FinderHandler $successor)
    {
        if (isset($this->successor)) {
            $this->successor->append($successor);
        }
        
        $this->successor = $successor;
    }
    
    final public function has(Request $request)
    {
        $this->found = $this->handle($request);
        
        if (!$this->found) {
            if (!is_null($this->successor)) {
                $this->found = $this->successor->has($request);
            }
        }

        return $this->found;
    }
    
    abstract protected function handle(Request $request);
}
