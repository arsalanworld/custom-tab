<?php
namespace Arsal\CustomTab\Model;

class TabConfig
{
    /**
     * @var array $tabs
     */
    private $tabs = [
        'tabA' =>  [
            'title'         =>  'Custom Tab A',
            'description'      =>  'Custom Tab A is right here !'
        ],
        'tabB'  =>  [
            'title' =>  'New Custom Tab B',
            'description'   =>  '<p>Tab B information goes here.</p>'
        ],
        'tabC'  =>  [
            'title' =>  'Test Custom Tab C',
            'description'   =>  '<p>This is C Tab now</p>'
        ]
    ];

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

}
