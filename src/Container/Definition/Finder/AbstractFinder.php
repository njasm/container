<?php

namespace Njasm\Container\Definition\Finder;

abstract class AbstractFinder
{
    protected $successor;
    protected $found;
    
    final public function append(AbstractFinder $successor)
    {
        if (isset($this->successor)) {
            $this->successor->append($successor);
        }
        
        $this->successor = $successor;
    }
    
    final public function has(FindRequest $request)
    {
        $this->found = $this->handle($request);
        
        if (!$this->found) {
            if (!is_null($this->successor)) {
                $this->found = $this->successor->has($request);
            }
        }

        return $this->found;
    }
    
    abstract protected function handle(FindRequest $request);
}
