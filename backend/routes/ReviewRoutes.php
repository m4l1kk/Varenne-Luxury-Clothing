<?php

/**
 * @OA\Get(
 *     path="/products/{id}/reviews", tags={"reviews"}, summary="Reviews of one product with the average rating",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="Object with summary and reviews"),
 *     @OA\Response(response=400, description="Product does not exist")
 * )
 */
Flight::route('GET /products/@id:[0-9]+/reviews', function ($id) {
    Flight::json(Flight::reviewService()->getByProductId((int) $id));
});

/**
 * @OA\Get(
 *     path="/reviews", tags={"reviews"}, summary="List every review (for moderation)",
 *     @OA\Response(response=200, description="Array of reviews")
 * )
 */
Flight::route('GET /reviews', function () {
    Flight::json(Flight::reviewService()->getAll());
});

/**
 * @OA\Get(
 *     path="/reviews/{id}", tags={"reviews"}, summary="Get one review",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The review"),
 *     @OA\Response(response=404, description="Review not found")
 * )
 */
Flight::route('GET /reviews/@id:[0-9]+', function ($id) {
    Flight::json(Flight::reviewService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/reviews", tags={"reviews"}, summary="Write a review (one per user per product)",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"user_id", "product_id", "rating"},
 *         @OA\Property(property="user_id", type="integer", example=3),
 *         @OA\Property(property="product_id", type="integer", example=1),
 *         @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
 *         @OA\Property(property="comment", type="string", example="Warm and incredibly soft.")
 *     )),
 *     @OA\Response(response=201, description="The created review"),
 *     @OA\Response(response=400, description="Missing or invalid field"),
 *     @OA\Response(response=409, description="Already reviewed")
 * )
 */
Flight::route('POST /reviews', function () {
    Flight::json(Flight::reviewService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/reviews/{id}", tags={"reviews"}, summary="Edit the rating or comment of a review",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
 *         @OA\Property(property="comment", type="string")
 *     )),
 *     @OA\Response(response=200, description="The updated review"),
 *     @OA\Response(response=400, description="Invalid rating"),
 *     @OA\Response(response=404, description="Review not found")
 * )
 */
Flight::route('PUT /reviews/@id:[0-9]+', function ($id) {
    Flight::json(Flight::reviewService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/reviews/{id}", tags={"reviews"}, summary="Delete a review",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Review not found")
 * )
 */
Flight::route('DELETE /reviews/@id:[0-9]+', function ($id) {
    Flight::reviewService()->delete((int) $id);
    Flight::json(['message' => "Review $id deleted"]);
});
