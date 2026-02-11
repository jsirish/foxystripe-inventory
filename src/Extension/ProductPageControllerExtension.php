<?php

namespace Dynamic\FoxyStripe\Extension;

use Dynamic\FoxyStripe\Model\ProductCartReservation;
use Dynamic\FoxyStripe\Page\ProductPage;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\View\Requirements;

/**
 * Class ProductPageControllerExtension
 * @package Dynamic\Sheeps\ProductCartExpiry\Extension
 */
class ProductPageControllerExtension extends Extension
{
    /**
     *
     */
    public function onAfterInit()
    {
        Requirements::javascript('dynamic/foxystripe-inventory: client/dist/javascript/scripts.min.js');
    }

    /**
     * @var array
     */
    private static $allowed_actions = [
        'reserveproduct' => '->validReservation',
    ];

    /**
     * @param HTTPRequest|null $request
     * @return bool
     */
    public function validReservation(HTTPRequest $request = null)
    {
        if (!$request instanceof HTTPRequest) {
            $request = Controller::curr()->getRequest();
        }


        return true;
    }

    /**
     * @param HTTPRequest $request
     */
    public function reserveproduct(HTTPRequest $request)
    {
        $code = $request->getVar('code');
        $id = $request->getVar('id');
        $expires = $request->getVar('expires');

        if (!$code || !$id || !$expires) {
            return false;
        }

        if (!$this->isProductReserved($code, $id, $expires)) {
            $this->addProductReservation($code, $id, $expires);
        }
    }

    /**
     * @param $code
     * @param $id
     * @param $expires
     * @return bool
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function addProductReservation($code, $id, $expires)
    {
        $product = ProductPage::get()->filter('Code', $code)->first();

        $reservation = ProductCartReservation::create();
        $reservation->ReservationCode = $this->getReservationHash($code, $id, $expires);
        $reservation->CartProductID = $id;
        $reservation->Code = $code;
        $reservation->Expires = date('Y-m-d H:i:s', $expires);
        if ($product !== null) {
            $reservation->ProductID = $product->ID;
        }

        return $reservation->write() > 0;
    }

    /**
     * @param $code
     * @param $id
     * @param $expires
     * @return \SilverStripe\ORM\DataObject
     */
    protected function isProductReserved($code, $id, $expires)
    {
        return ProductCartReservation::get()->filter('ReservationCode', $this->getReservationHash($code, $id, $expires))->first();
    }

    /**
     * @param $code
     * @param $id
     * @param $expires
     * @return string
     */
    protected function getReservationHash($code, $id, $expires)
    {
        return md5($code . $id . $expires);
    }
}
