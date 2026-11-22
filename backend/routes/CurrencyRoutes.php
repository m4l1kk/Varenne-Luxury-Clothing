<?php

/**
 * @OA\Get(
 *     path="/products/{id}/prices", tags={"currency"},
 *     summary="External API: the product price converted from EUR to USD, GBP, CHF and BAM",
 *     description="Live rates come from Frankfurter (ECB). BAM uses the fixed rate 1 EUR = 1.95583 BAM. If Frankfurter is down or slower than 5 seconds, the failure is logged and 503 is returned.",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Prices per currency and the date of the rates"),
 *     @OA\Response(response=404, description="Product not found"),
 *     @OA\Response(response=503, description="Exchange rates are not available right now")
 * )
 */
Flight::route('GET /products/@id:[0-9]+/prices', function ($id) {
    Flight::json(Flight::currencyService()->getProductPrices((int) $id));
});
