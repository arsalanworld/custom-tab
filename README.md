# Arsal_CustomTab - 1.0.0

###### Installation:
> composer require arsal/custom-tab:dev-master


Demonstration of Creating Dynamic Tabs in magento2-module.


![Dynamic Tabs](https://knowthemage.com/wp-content/uploads/2019/03/screen_kw_post.png)


* Lets go a head and initialize our module:
```
<?xml version="1.0" ?>
 
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Arsal_CustomTab" setup_version="1.0.0">
        <sequence>
            <module name="Magento_Catalog" />
        </sequence>
    </module>
</config>
```

* Register our module: (`Arsal/Customtab/registration.php`):
```
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Arsal_CustomTab',
    __DIR__
);
```

* For demonstration purposes I’m creating only a Config model here to retrieve values quickly in the form of array, which will be further used to push to tabs.
So let go ahead and create a new Config model: `Arsal/CustomTab/Model/TabConfig.php`
```
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

```

* Next step is the injection of this data to block and adding these blocks will be treated as child of block :
`product.info.details`

* To achieve this we need to observe the generation of layouts after event i.e `layout_generate_blocks_after`. So let’s move a head and create an event observer.

* Create events.xml inside 'Arsal/CustomTab/etc/' : `Arsal/CustomTab/etc/events.xml`

```
<?xml version="1.0"?>
<!--
/**
* @module: Arsal_CustomTab
* @author: Arsalan Ajmal
*/
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="layout_generate_blocks_after">
        <observer name="arsal_layout_generate_blocks_after" instance="Arsal\CustomTab\Observer\NewTab" />
    </event>
</config>
```
* Now create observer class NewTab.php inside Arsal/CustomTab/Observer: 
`Arsal/CustomTab/Observer/NewTab.php`

```
<?php
/**
 * @author Arsalan Ajmal
 * @module Arsal_CustomTab
 */
namespace Arsal\CustomTab\Observer;
 
use Arsal\CustomTab\Model\TabConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class NewTab implements ObserverInterface
{
    /**
     * @var string PARENT_BlOCK_NAME
     */
    const PARENT_BlOCK_NAME = 'product.info.details';
 
    /**
     * @var string RENDERING_TEMPLATE
     */
    const RENDERING_TEMPLATE = 'Arsal_CustomTab::tab_renderer.phtml';
 
    /**
     * @var TabConfig $tabs
     */
    private $tabs;
 
    /**
     * NewTab constructor.
     * @param TabConfig $tabs
     */
    public function __construct(TabConfig $tabs)
    {
        $this->tabs = $tabs;
    }
 
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $blocks = $layout->getAllBlocks();
 
        foreach ($blocks as $key => $block) {
            /** @var \Magento\Framework\View\Element\Template $block */
            if ($block->getNameInLayout() == self::PARENT_BlOCK_NAME) {
 
                foreach ($this->tabs->getTabs() as $key => $tab) {
                    $block->addChild(
                        $key,
                        \Magento\Catalog\Block\Product\View::class,
                        [
                            'template' => self::RENDERING_TEMPLATE,
                            'title'     =>  $tab['title'],
                            'jsLayout'      =>  [
                                $tab
                            ]
                        ]
                    );
                }
            }
        }
    }
}
```

* Now create phtml rendering template: `Arsal/CustomTab/view/frontend/templates/tab_renderer.phtml`

```
<?php
/**
 * @var \Magento\Catalog\Block\Product\View $block
 */
?>
<?php
if (!empty($block->getJsLayout())) {
    $jsLayout = \Zend_Json::decode($block->getJsLayout());
    foreach ($jsLayout as $layout) {
        if (isset($layout['type']) && 'template' === $layout['type'] && isset($layout['data'])){
            echo $this->getLayout()->createBlock($layout['data']['type'])
                ->setDisplayType($layout['data']['name'])
                ->setTemplate($layout['data']['template'])->toHtml();
        } else {
            ?>
            <h1><?= $layout['title']; ?></h1>
            <div><?= $layout['description']; ?></div>
            <?php
        }
    }
}
```

* We are step behind rendering the blocks. To render these blocks we need to add block names to grouped child data. The best way we can do with it is to add these blocks name via interceptor (plugin) to grouped data.
  
  Here we can also apply condition like if product sku is `VA20-SI-NA` then don’t display custom tabs and we apply many more.
  
* First create plugin configuration: 
    `Arsal/CustomTab/etc/frontend/di.xml`

```
<?xml version="1.0"?>
<!--
/**
* @module: Arsal_CustomTab
* @author: Arsalan Ajmal
*/
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
 
    <type name="Magento\Catalog\Block\Product\View\Details">
        <plugin name="arsal_product_view_description" type="Arsal\CustomTab\Plugin\Description" />
    </type>
 
</config>
```

* Create Plugin instance class: 
    `Arsal/CustomTab/Plugin/Description.php`

```
<?php
namespace Arsal\CustomTab\Plugin;
 
use Arsal\CustomTab\Model\TabConfig;
 
class Description
{
    /**
     * @var TabConfig $tabs
     */
    private $tabs;
 
    /**
     * Description constructor.
     * @param TabConfig $tabs
     */
    public function __construct(
        TabConfig $tabs
    ) {
        $this->tabs = $tabs;
    }
 
    /**
     * @param \Magento\Catalog\Block\Product\View\Details $subject
     * @param array $result
     * @return array
     */
    public function afterGetGroupSortedChildNames(
        \Magento\Catalog\Block\Product\View\Details $subject,
        $result
    ) {
        if (!empty($this->tabs->getTabs())) {
            foreach ($this->tabs->getTabs() as $key => $tab) {
                $sortOrder = isset($tab['sortOrder']) ? $tab['sortOrder'] : 45;
                $result = array_merge($result, [ $sortOrder => 'product.info.details.' . $key]);
            }
        }
        return $result;
    }
}
```
Now create template c renderer template:
```
<?php
/**
 * @var \Magento\Framework\View\Element\Template $block
 */
?>
<h3>Custom Block</h3>
<div>
    <h4>What is Lorem Ipsum?</h4>
    <p>
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
    </p>
</div>
```