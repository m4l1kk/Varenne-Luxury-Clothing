<?php

/**
 * @OA\Get(
 *     path="/products", tags={"products"}, summary="List products (all, by category, or by search term)",
 *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", example="silk")),
 *     @OA\Response(response=200, description="Array of products"),
 *     @OA\Response(response=400, description="Category does not exist")
 * )
 */
Flight::route('GET /products', function () {
    $query = Flight::request()->query;
    Flight::json(Flight::productService()->getFiltered($query['category_id'], $query['search']));
});

/**
 * @OA\Get(
 *     path="/products/{id}", tags={"products"}, summary="Get one product",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The product"),
 *     @OA\Response(response=404, description="Product not found")
 * )
 */
Flight::route('GET /products/@id:[0-9]+', function ($id) {
    Flight::json(Flight::productService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/products", tags={"products"}, summary="Create a product",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"category_id", "name", "price"},
 *         @OA\Property(property="category_id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="Leather Gloves"),
 *         @OA\Property(property="description", type="string", example="Lambskin gloves lined with cashmere."),
 *         @OA\Property(property="price", type="number", example=290.00),
 *         @OA\Property(property="image_url", type="string", example=null),
 *         @OA\Property(property="is_active", type="boolean", example=true)
 *     )),
 *     @OA\Response(response=201, description="The created product"),
 *     @OA\Response(response=400, description="Missing or invalid field")
 * )
 */
Flight::route('POST /products', function () {
    Flight::json(Flight::productService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/products/{id}", tags={"products"}, summary="Update a product (send only the fields to change)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="category_id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="price", type="number", example=2390.00),
 *         @OA\Property(property="image_url", type="string"),
 *         @OA\Property(property="is_active", type="boolean")
 *     )),
 *     @OA\Response(response=200, description="The updated product"),
 *     @OA\Response(response=400, description="Invalid field"),
 *     @OA\Response(response=404, description="Product not found")
 * )
 */
Flight::route('PUT /products/@id:[0-9]+', function ($id) {
    Flight::json(Flight::productService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/products/{id}", tags={"products"}, summary="Delete a product (refused if it was ordered; hide it instead)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Product not found"),
 *     @OA\Response(response=409, description="The product is part of orders")
 * )
 */
Flight::route('DELETE /products/@id:[0-9]+', function ($id) {
    Flight::productService()->delete((int) $id);
    Flight::json(['message' => "Product $id deleted"]);
});
