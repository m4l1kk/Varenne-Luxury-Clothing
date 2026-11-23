<?php

/**
 * @OA\Get(
 *     path="/categories", tags={"categories"}, summary="List categories with their product count",
 *     @OA\Response(response=200, description="Array of categories")
 * )
 */
Flight::route('GET /categories', function () {
    Flight::json(Flight::categoryService()->getAll());
});

/**
 * @OA\Get(
 *     path="/categories/{id}", tags={"categories"}, summary="Get one category",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The category"),
 *     @OA\Response(response=404, description="Category not found")
 * )
 */
Flight::route('GET /categories/@id:[0-9]+', function ($id) {
    Flight::json(Flight::categoryService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/categories", tags={"categories"}, summary="Create a category (the slug is made from the name if not sent)",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"name"},
 *         @OA\Property(property="name", type="string", example="Evening Wear"),
 *         @OA\Property(property="slug", type="string", example="evening-wear"),
 *         @OA\Property(property="description", type="string", example="Gowns and tuxedos.")
 *     )),
 *     @OA\Response(response=201, description="The created category"),
 *     @OA\Response(response=400, description="Missing name"),
 *     @OA\Response(response=409, description="Name or slug already used")
 * )
 */
Flight::route('POST /categories', function () {
    Flight::json(Flight::categoryService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/categories/{id}", tags={"categories"}, summary="Update a category",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=3)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="slug", type="string"),
 *         @OA\Property(property="description", type="string", example="Bags, scarves and hats.")
 *     )),
 *     @OA\Response(response=200, description="The updated category"),
 *     @OA\Response(response=404, description="Category not found"),
 *     @OA\Response(response=409, description="Name or slug already used")
 * )
 */
Flight::route('PUT /categories/@id:[0-9]+', function ($id) {
    Flight::json(Flight::categoryService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/categories/{id}", tags={"categories"}, summary="Delete a category (refused while it has products)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Category not found"),
 *     @OA\Response(response=409, description="The category still has products")
 * )
 */
Flight::route('DELETE /categories/@id:[0-9]+', function ($id) {
    Flight::categoryService()->delete((int) $id);
    Flight::json(['message' => "Category $id deleted"]);
});
