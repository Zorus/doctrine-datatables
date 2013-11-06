<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class BooleanField extends ChoiceField
{
    public function format(array $values)
    {
        return (bool) $this->getValue($values);
    }

    public function filter(QueryBuilder $qb)
    {
        return $qb->setParameter($this->getName(), $this->getSearch())
            ->expr()->eq($this->getFullName(), ':'.$this->getName());
    }
} 