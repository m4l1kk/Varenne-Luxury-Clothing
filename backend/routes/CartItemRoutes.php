<?php

/**
 * @OA\Get(
 *     path="/users/{id}/cart", tags={"cart"}, summary="The user's bag with line totals and the bag total",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="Object with items and total"),
 *     @OA\Response(response=400, description="User does not exist")
 * )
 */
Flight::route('GET /users/@id:[0-9]+/cart', function ($id) {
    Flight::json(Flight::cartService()->getByUserId((int) $id));
});

/**
 * @OA\Delete(
 *     path="/users/{id}/cart", tags={"cart"}, summary="Empty the user's bag",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="Number of removed lines")
 * )
 */
Flight::route('DELETE /users/@id:[0-9]+/cart', function ($id) {
    Flight::json(['removed' => Flight::cartService()->clear((int) $id)]);
});

/**
 * @OA\Get(
 *     path="/cart-items/{id}", tags={"cart"}, summary="Get one bag line",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The bag line"),
 *     @OA\Response(response=404, description="Cart item not found")
 * )
 */
Flight::route('GET /cart-items/@id:[0-9]+', function ($id) {
    Flight::json(Flight::cartService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/cart-items", tags={"cart"}, summary="Add to bag (adding the same variant again raises its quantity)",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"user_id", "variant_id"},
 *         @OA\Property(property="user_id", type="integer", example=2),
 *         @OA\Property(property="variant_id", type="integer", example=9),
 *         @OA\Property(property="quantity", type="integer", example=1)
 *     )),
 *     @OA\Response(response=201, description="The bag line"),
 *     @OA\Response(response=400, description="Missing or invalid field"),
 *     @OA\Response(response=409, description="Not enough stock")
 * )
 */
Flight::route('POST /cart-items', function () {
    Flight::json(Flight::cartService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/cart-items/{id}", tags={"cart"}, summary="Change the quantity of a bag line",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"quantity"},
 *         @OA\Property(property="quantity", type="integer", example=2)
 *     )),
 *     @OA\Response(response=200, description="The updated bag line"),
 *     @OA\Response(response=404, description="Cart item not found"),
 *     @OA\Response(response=409, description="Not enough stock")
 * )
 */
Flight::route('PUT /cart-items/@id:[0-9]+', function ($id) {
    Flight::json(Flight::cartService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/cart-items/{id}", tags={"cart"}, summary="Remove a line from the bag",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Cart item not found")
 * )
 */
Flight::route('DELETE /cart-items/@id:[0-9]+', function ($id) {
    Flight::cartService()->delete((int) $id);
    Flight::json(['message' => "Cart item $id deleted"]);
});

/**
 * @OA\Get(
 *     path="/cart-items", tags={"cart"}, summary="List every bag line (all users)",
 *     @OA\Response(response=200, description="Array of cart items")
 * )
 */
Flight::route('GET /cart-items', function () {
    Flight::json(Flight::cartService()->getAll());
});
