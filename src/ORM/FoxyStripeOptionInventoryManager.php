<?php

namespace Dynamic\FoxyStripe\ORM;

use Dynamic\FoxyStripe\Model\OrderDetail;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Core\Extension;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Class FoxyStripeOptionInventoryManager
 * @package Dynamic\FoxyStripe\ORM
 *
 * @property bool $ControlInventory
 * @property int $PurchaseLimit
 *
 * @property-read \Dynamic\FoxyStripe\Model\OptionItem $owner
 */
class FoxyStripeOptionInventoryManager extends Extension
{
    /**
     * @var array
     */
    private static $db = [
        'ControlInventory' => 'Boolean',
        'PurchaseLimit' => 'Int',
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
    public function getIsOptionAvailable()
    {
        if ($this->getHasInventory()) {
            return $this->owner->PurchaseLimit > $this->getNumberPurchased();
        }
        return true;
    }

    /**
     * @return int
     */
    public function getNumberAvailable()
    {
        if ($this->getIsOptionAvailable()) {
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
            foreach ($this->getOrders() as $order) {
                $ct += $order->Quantity;
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
            return OrderDetail::get()->filter('OrderOptions.ID', $this->owner->ID);
        }
        return false;
    }

    /**
     * @param $available
     */
    public function updateOptionAvailability(&$available)
    {
        if ($this->getHasInventory()) {
            $available = $this->getIsOptionAvailable();
        }
    }
}