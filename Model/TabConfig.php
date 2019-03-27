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
            'description'   =>  'Custom Tab A is right here !',
            'sortOrder'     =>  50
        ],
        'tabB'  =>  [
            'title'         =>  'Recently Viewed',
            'type'          =>  'template',
            'data'          =>  [
                "type"      =>  "Magento\Reports\Block\Product\Widget\Viewed",
                "name"      =>  "custom.recently.view.products",
                "template"  =>  "Magento_Reports::widget/viewed/content/viewed_list.phtml"
            ],
            'description'   =>  '',
            'sortOrder'     =>  45
        ],
        'tabC'  => [
            'title'         =>  'Lorem Ipsum Tab',
            'type'          =>  'template',
            'data'          =>  [
                "type"      =>  "Magento\Framework\View\Element\Template",
                "name"      =>  "lorem.ipsum",
                "template"  =>  "Arsal_CustomTab::template_c.phtml"
            ],
            'description'   =>  '',
            'sortOrder'     =>  45
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
