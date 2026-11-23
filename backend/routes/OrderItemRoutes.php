<?php

/**
 * @OA\Get(
 *     path="/orders/{id}/items", tags={"order items"}, summary="Lines of one order with product name, size and colour",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=5)),
 *     @OA\Response(response=200, description="Array of order items"),
 *     @OA\Response(response=400, description="Order does not exist")
 * )
 */
Flight::route('GET /orders/@id:[0-9]+/items', function ($id) {
    Flight::json(Flight::orderItemService()->getByOrderId((int) $id));
});

/**
 * @OA\Get(
 *     path="/order-items", tags={"order items"}, summary="List every order line",
 *     @OA\Response(response=200, description="Array of order items")
 * )
 */
Flight::route('GET /order-items', function () {
    Flight::json(Flight::orderItemService()->getAll());
});

/**
 * @OA\Get(
 *     path="/order-items/{id}", tags={"order items"}, summary="Get one order line",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The order item"),
 *     @OA\Response(response=404, description="Order item not found")
 * )
 */
Flight::route('GET /order-items/@id:[0-9]+', function ($id) {
    Flight::json(Flight::orderItemService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/order-items", tags={"order items"}, summary="Add a line to an existing order (the order total is recalculated)",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"order_id", "variant_id"},
 *         @OA\Property(property="order_id", type="integer", example=4),
 *         @OA\Property(property="variant_id", type="integer", example=14),
 *         @OA\Property(property="quantity", type="integer", example=1),
 *         @OA\Property(property="unit_price", type="number", description="Optional; the product price is used if it is not sent")
 *     )),
 *     @OA\Response(response=201, description="The created order item"),
 *     @OA\Response(response=400, description="Missing or invalid field")
 * )
 */
Flight::route('POST /order-items', function () {
    Flight::json(Flight::orderItemService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/order-items/{id}", tags={"order items"}, summary="Change the quantity, variant or price of an order line",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=6)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="variant_id", type="integer"),
 *         @OA\Property(property="quantity", type="integer", example=2),
 *         @OA\Property(property="unit_price", type="number")
 *     )),
 *     @OA\Response(response=200, description="The updated order item"),
 *     @OA\Response(response=400, description="Invalid field"),
 *     @OA\Response(response=404, description="Order item not found")
 * )
 */
Flight::route('PUT /order-items/@id:[0-9]+', function ($id) {
    Flight::json(Flight::orderItemService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/order-items/{id}", tags={"order items"}, summary="Remove a line from an order (the order total is recalculated)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Order item not found")
 * )
 */
Flight::route('DELETE /order-items/@id:[0-9]+', function ($id) {
    Flight::orderItemService()->delete((int) $id);
    Flight::json(['message' => "Order item $id deleted"]);
});
