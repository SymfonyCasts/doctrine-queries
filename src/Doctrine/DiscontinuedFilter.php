<?php

namespace App\Doctrine;

use App\Entity\FortuneCookie;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DiscontinuedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        //dd($targetEntity, $targetTableAlias);
        if ($targetEntity->getReflectionClass()->name !== FortuneCookie::class) {
            return '';
        }

//        return sprintf('%s.discontinued = false', $targetTableAlias);
        return sprintf('%s.discontinued = %s', $targetTableAlias, $this->getParameter('discontinued'));
    }
}
