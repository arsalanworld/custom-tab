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
     * @param \Magento\Catalog\Block\Product\View\Description $subject
     * @param array $result
     * @return array
     */
    public function afterGetGroupChildNames(
        \Magento\Catalog\Block\Product\View\Description $subject,
        $result
    ) {

        if ($subject->getProduct()->getData('sku') != 'VA20-SI-NA') {
            foreach ($this->tabs->getTabs() as $key => $tab) {
                $result [] = 'product.info.details.' . $key;
            }
        }

        return $result;
    }
}
