<?php

namespace App\Doctrine;

use App\Entity\Task;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class StatusFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias) {
        //if ($targetEntity->getReflectionClass()->name !== Task::class) {
            return $targetTableAlias.'.status = ' . $this->getParameter('status');
        //}
    }
}