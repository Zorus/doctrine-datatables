<?php
namespace NeuroSYS\DoctrineDatatables\Tests;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Portability\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use NeuroSYS\DoctrineDatatables\Datatable;
use NeuroSYS\DoctrineDatatables\DatatableBuilder;
use NeuroSYS\DoctrineDatatables\Field\Entity;
use NeuroSYS\DoctrineDatatables\FieldBuilder;

class BuilderTest extends BaseTestCase
{
    public function testTextFieldWithLimit()
    {
        $request  = $this->createSearchRequest(array('iDisplayLength' => 2), array('name', 'product.price'));
        $builder  = new DatatableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->add("text")
            ->add("number")
        ;

        $response = $builder->getDatatable()
            ->getResponseArray();

        $this->assertEquals(2, count($response['aaData']));
        $this->assertEquals(3, $response['iTotalRecords']);
        $this->assertEquals(3, $response['iTotalRecords']);
        $this->assertEquals(3, $response['iTotalDisplayRecords']);
    }

    public function testRangeField()
    {
        $request  = $this->createSearchRequest(array(), array('name', 'product.price'), array('Generation', '10,'));
        $builder  = new DatatableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->add("text")
            ->add("number")
        ;

        $result = $builder->getDatatable()
            ->getResult();

        $this->assertEquals(array(
            array (
                'name' => 'CPU I7 Generation',
                'price' => '1000',
            ),
        ), $result);
    }

    public function testChoiceField()
    {
        $ids = $this->_em->getConnection()->query("SELECT id FROM features")->fetchAll();
        $ids = array_map('current', $ids);

        $request  = $this->createSearchRequest(array(), array('id', 'name'), array(implode(',', $ids)));
        $builder  = new DatatableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->add("choice")
            ->add("text")
        ;

        $result = $builder->getDatatable()
            ->getResult();

        $this->assertEquals(count($ids), count($result));
    }

    public function testMultiFieldWithSort()
    {
        $request  = $this->createSearchRequest(array('sSortDir_0' => 'desc'), array('fullName', 'product.price'), array());
        $builder  = new DatatableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature')
            ->multi('fullName')
                ->add('text', 'product.name')
                ->add('text', 'name')
            ->end()
            ->add("number")
        ;

        $results = $builder->getDatatable()
            ->getResult();
        $results = array_map('current', $results);

        // fetch real results
        $sorted = $this->_em->getConnection()->query("SELECT p.name as p_name, f.name as f_name FROM features f LEFT JOIN products p ON p.id = f.product_id ORDER BY p.name DESC, f.name DESC")->fetchAll();
        foreach ($sorted as $i => $row) {
            $sorted[$i] = $row['p_name'] . ' ' . $row['f_name'];
        }

        $this->assertEquals($sorted, $results);
    }
}