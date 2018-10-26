<?php
namespace Cake;

/**
 * Hello World.
 *
 * This is a description.
 */
class Foo extends Bar
{
    /**
     * What are your thoughts?
     *
     * @var array $brain
     */
    public $brain = array();

    /**
     * Tell me your thoughts.
     *
     * @return void
     */
    public function dumpThoughts()
    {
        foreach ($thoughts as $thought) {
            echo $thought;
        }
    }
}
