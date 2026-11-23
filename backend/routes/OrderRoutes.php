<?php

/**
 * @OA\Get(
 *     path="/orders", tags={"orders"}, summary="List orders, newest first (optionally by status)",
 *     @OA\Parameter(name="status", in="query", required=false,
 *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})),
 *     @OA\Response(response=200, description="Array of orders"),
 *     @OA\Response(response=400, description="Unknown status")
 * )
 */
Flight::route('GET /orders', function () {
    Flight::json(Flight::orderService()->getFiltered(Flight::request()->query['status']));
});

/**
 * @OA\Get(
 *     path="/users/{id}/orders", tags={"orders"}, summary="Order history of one user",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="Array of orders"),
 *     @OA\Response(response=400, description="User does not exist")
 * )
 */
Flight::route('GET /users/@id:[0-9]+/orders', function ($id) {
    Flight::json(Flight::orderService()->getByUserId((int) $id));
});

/**
 * @OA\Get(
 *     path="/orders/{id}", tags={"orders"}, summary="Get one order with its lines",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=5)),
 *     @OA\Response(response=200, description="The order with an items array"),
 *     @OA\Response(response=404, description="Order not found")
 * )
 */
Flight::route('GET /orders/@id:[0-9]+', function ($id) {
    Flight::json(Flight::orderService()->getWithItems((int) $id));
});

/**
 * @OA\Post(
 *     path="/orders", tags={"orders"}, summary="Checkout: turn the user's bag into an order",
 *     description="Prices and the total are read from the database. The order, its lines, the stock change and emptying the bag run in one transaction.",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"user_id"},
 *         @OA\Property(property="user_id", type="integer", example=2),
 *         @OA\Property(property="address_id", type="integer", example=1)
 *     )),
 *     @OA\Response(response=201, description="The created order with its lines"),
 *     @OA\Response(response=400, description="Empty bag, unknown user or someone else's address"),
 *     @OA\Response(response=409, description="Not enough stock")
 * )
 */
Flight::route('POST /orders', function () {
    Flight::json(Flight::orderService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/orders/{id}", tags={"orders"}, summary="Change the status or delivery address of an order",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=4)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}, example="shipped"),
 *         @OA\Property(property="address_id", type="integer")
 *     )),
 *     @OA\Response(response=200, description="The updated order"),
 *     @OA\Response(response=400, description="Unknown status or address"),
 *     @OA\Response(response=404, description="Order not found")
 * )
 */
Flight::route('PUT /orders/@id:[0-9]+', function ($id) {
    Flight::json(Flight::orderService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/orders/{id}", tags={"orders"}, summary="Delete an order and its lines",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Order not found")
 * )
 */
Flight::route('DELETE /orders/@id:[0-9]+', function ($id) {
    Flight::orderService()->delete((int) $id);
    Flight::json(['message' => "Order $id deleted"]);
});
