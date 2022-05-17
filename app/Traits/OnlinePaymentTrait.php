<?php
 
namespace App\Traits;

use Exception;

trait OnlinePaymentTrait {

    /**
     * Get/check user is exist in conekta or not
     */
    public function checkUserExistOnConekta($client)
    {
        try {
            $customerId = '';
            if(count($client->onlinePaymentCustomerDetail)) {
                $allCustomer = \Conekta\Customer::all();
                if(count($allCustomer)) {
                    $pluckAllCustomerId = collect($allCustomer)->pluck('id')->toArray();
                    $existCustomerId = $client->onlinePaymentCustomerDetail->pluck('conekta_customer_id')->toArray();
                    $existId = array_intersect($pluckAllCustomerId, $existCustomerId);
                    $customerId = array_values($existId)[0] ?? '';
                }
            } 
            return $customerId;
        } catch (\Conekta\AuthenticationError $e){
            return 'error code';
        } catch (\Conekta\ApiError $e){
            return 'error code';
        } catch (\Conekta\ProcessingError $e){
            return 'error code';
        } catch (\Conekta\ParameterValidationError $e){
            return 'error code';
        } catch (\Conekta\Handler $e){
            return 'error code';
        } catch (Exception $e){
            return 'error code';
        }
    }
}