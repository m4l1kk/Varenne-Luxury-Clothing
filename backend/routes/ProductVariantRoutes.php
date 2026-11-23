<?php

/**
 * @OA\Get(
 *     path="/products/{id}/variants", tags={"variants"}, summary="Sizes and colours of one product, with stock",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Array of variants"),
 *     @OA\Response(response=400, description="Product does not exist")
 * )
 */
Flight::route('GET /products/@id:[0-9]+/variants', function ($id) {
    Flight::json(Flight::variantService()->getByProductId((int) $id));
});

/**
 * @OA\Get(
 *     path="/variants", tags={"variants"}, summary="List every variant (stock overview)",
 *     @OA\Response(response=200, description="Array of variants")
 * )
 */
Flight::route('GET /variants', function () {
    Flight::json(Flight::variantService()->getAll());
});

/**
 * @OA\Get(
 *     path="/variants/{id}", tags={"variants"}, summary="Get one variant",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The variant"),
 *     @OA\Response(response=404, description="Variant not found")
 * )
 */
Flight::route('GET /variants/@id:[0-9]+', function ($id) {
    Flight::json(Flight::variantService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/variants", tags={"variants"}, summary="Add a size/colour to a product",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"product_id", "size", "color", "sku"},
 *         @OA\Property(property="product_id", type="integer", example=1),
 *         @OA\Property(property="size", type="string", example="L"),
 *         @OA\Property(property="color", type="string", example="Camel"),
 *         @OA\Property(property="sku", type="string", example="VAR-001-L-CAM"),
 *         @OA\Property(property="stock", type="integer", example=2)
 *     )),
 *     @OA\Response(response=201, description="The created variant"),
 *     @OA\Response(response=400, description="Missing or invalid field"),
 *     @OA\Response(response=409, description="SKU already used")
 * )
 */
Flight::route('POST /variants', function () {
    Flight::json(Flight::variantService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/variants/{id}", tags={"variants"}, summary="Update a variant, e.g. its stock",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="size", type="string"),
 *         @OA\Property(property="color", type="string"),
 *         @OA\Property(property="sku", type="string"),
 *         @OA\Property(property="stock", type="integer", example=7)
 *     )),
 *     @OA\Response(response=200, description="The updated variant"),
 *     @OA\Response(response=400, description="Invalid field"),
 *     @OA\Response(response=404, description="Variant not found")
 * )
 */
Flight::route('PUT /variants/@id:[0-9]+', function ($id) {
    Flight::json(Flight::variantService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/variants/{id}", tags={"variants"}, summary="Delete a variant (refused if it was ordered)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Variant not found"),
 *     @OA\Response(response=409, description="The variant is part of orders")
 * )
 */
Flight::route('DELETE /variants/@id:[0-9]+', function ($id) {
    Flight::variantService()->delete((int) $id);
    Flight::json(['message' => "Variant $id deleted"]);
});
