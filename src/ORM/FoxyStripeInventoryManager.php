<?php

namespace Dynamic\FoxyStripe\ORM;

use Dynamic\FoxyStripe\Model\OrderDetail;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Class FoxyStripeInventoryManager
 * @package Dynamic\FoxyStripe\ORM
 *
 * @property boolean $ControlInventory
 * @property int $PurchaseLimit
 * @property int $EmbargoLimit
 *
 * @property-read \Dynamic\FoxyStripe\Page\ProductPage|\Dynamic\FoxyStripe\ORM\FoxyStripeInventoryManager $owner
 */
class FoxyStripeInventoryManager extends Extension
{
    /**
     * @var array
     */
    private static $db = [
        'ControlInventory' => 'Boolean',
        'PurchaseLimit' => 'Int',
        'EmbargoLimit' => 'Int',
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array(
            'PurchaseLimit',
            'EmbargoLimit',
            'NumberPurchased',
        ));

        $fields->addFieldsToTab('Root.Inventory', array(
            CheckboxField::create('ControlInventory', 'Control Inventory?')
            ->setDescription('limit the number of this product available for purchase'),
            Wrapper::create(
            NumericField::create('PurchaseLimit')
            ->setTitle('Number Available')
            ->setDescription('add to cart form will be disabled once number available equals purchased'),
            ReadonlyField::create('NumberPurchased', 'Purchased', $this->getNumberPurchased()) //,
            /*
     NumericField::create('EmbargoLimit')
     ->setTitle('Embargo Time')
     ->setDescription('time in seconds to reserve an item once added to cart')
     */
        )->displayIf('ControlInventory')->isChecked()->end(),
        ));
    }

    /**
     * @return bool
     */
    public function getHasInventory()
    {
        return $this->owner->ControlInventory && $this->owner->PurchaseLimit != 0;
    }

    /**
     * @return bool
     */
    public function getIsProductAvailable()
    {
        if ($this->owner->getHasInventory()) {
            return $this->owner->PurchaseLimit > $this->getNumberPurchased();
        }
        return true;
    }

    /**
     * @return int
     */
    public function getNumberAvailable()
    {
        if ($this->getIsProductAvailable()) {
            return (int)$this->owner->PurchaseLimit - (int)$this->getNumberPurchased();
        }
    }

    /**
     * @return int
     */
    public function getNumberPurchased()
    {
        $ct = 0;
        if ($this->getOrders()) {
            /** @var OrderDetail $order */
            foreach ($this->getOrders() as $order) {
                if ($order->OrderID !== 0) {
                    $ct += $order->Quantity;
                }
            }
        }

        return $ct;
    }

    /**
     * @return DataList
     */
    public function getOrders()
    {
        if ($this->owner->ID) {
            return OrderDetail::get()->filter('ProductID', $this->owner->ID);
        }
        return false;
    }
}