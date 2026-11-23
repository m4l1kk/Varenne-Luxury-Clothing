<?php

/**
 * @OA\Get(
 *     path="/users/{id}/wishlist", tags={"wishlist"}, summary="The user's saved pieces, newest first",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="Array of wishlist items"),
 *     @OA\Response(response=400, description="User does not exist")
 * )
 */
Flight::route('GET /users/@id:[0-9]+/wishlist', function ($id) {
    Flight::json(Flight::wishlistService()->getByUserId((int) $id));
});

/**
 * @OA\Post(
 *     path="/wishlist-items", tags={"wishlist"}, summary="Save a product to the wishlist",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"user_id", "product_id"},
 *         @OA\Property(property="user_id", type="integer", example=2),
 *         @OA\Property(property="product_id", type="integer", example=3)
 *     )),
 *     @OA\Response(response=201, description="The wishlist item"),
 *     @OA\Response(response=400, description="User or product does not exist"),
 *     @OA\Response(response=409, description="Already in the wishlist")
 * )
 */
Flight::route('POST /wishlist-items', function () {
    Flight::json(Flight::wishlistService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Delete(
 *     path="/wishlist-items/{id}", tags={"wishlist"}, summary="Remove a product from the wishlist",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Wishlist item not found")
 * )
 */
Flight::route('DELETE /wishlist-items/@id:[0-9]+', function ($id) {
    Flight::wishlistService()->delete((int) $id);
    Flight::json(['message' => "Wishlist item $id deleted"]);
});

/**
 * @OA\Get(
 *     path="/wishlist-items", tags={"wishlist"}, summary="List every wishlist item (all users)",
 *     @OA\Response(response=200, description="Array of wishlist items")
 * )
 */
Flight::route('GET /wishlist-items', function () {
    Flight::json(Flight::wishlistService()->getAll());
});

/**
 * @OA\Get(
 *     path="/wishlist-items/{id}", tags={"wishlist"}, summary="Get one wishlist item",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The wishlist item"),
 *     @OA\Response(response=404, description="Wishlist item not found")
 * )
 */
Flight::route('GET /wishlist-items/@id:[0-9]+', function ($id) {
    Flight::json(Flight::wishlistService()->getById((int) $id));
});

/**
 * @OA\Put(
 *     path="/wishlist-items/{id}", tags={"wishlist"}, summary="Swap the saved product for another one",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"product_id"},
 *         @OA\Property(property="product_id", type="integer", example=4)
 *     )),
 *     @OA\Response(response=200, description="The updated wishlist item"),
 *     @OA\Response(response=400, description="Product does not exist"),
 *     @OA\Response(response=404, description="Wishlist item not found"),
 *     @OA\Response(response=409, description="Already in the wishlist")
 * )
 */
Flight::route('PUT /wishlist-items/@id:[0-9]+', function ($id) {
    Flight::json(Flight::wishlistService()->update((int) $id, Flight::request()->data->getData()));
});
