<?php

/**
 * @OA\Get(
 *     path="/users/{id}/addresses", tags={"addresses"}, summary="Addresses of one user (default first)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="Array of addresses"),
 *     @OA\Response(response=400, description="User does not exist")
 * )
 */
Flight::route('GET /users/@id:[0-9]+/addresses', function ($id) {
    Flight::json(Flight::addressService()->getByUserId((int) $id));
});

/**
 * @OA\Get(
 *     path="/addresses/{id}", tags={"addresses"}, summary="Get one address",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="The address"),
 *     @OA\Response(response=404, description="Address not found")
 * )
 */
Flight::route('GET /addresses/@id:[0-9]+', function ($id) {
    Flight::json(Flight::addressService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/addresses", tags={"addresses"}, summary="Add an address (is_default=true makes it the only default)",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"user_id", "street", "city", "postal_code", "country"},
 *         @OA\Property(property="user_id", type="integer", example=2),
 *         @OA\Property(property="street", type="string", example="Titova 5"),
 *         @OA\Property(property="city", type="string", example="Sarajevo"),
 *         @OA\Property(property="postal_code", type="string", example="71000"),
 *         @OA\Property(property="country", type="string", example="Bosnia and Herzegovina"),
 *         @OA\Property(property="is_default", type="boolean", example=false)
 *     )),
 *     @OA\Response(response=201, description="The created address"),
 *     @OA\Response(response=400, description="Missing or invalid field")
 * )
 */
Flight::route('POST /addresses', function () {
    Flight::json(Flight::addressService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/addresses/{id}", tags={"addresses"}, summary="Update an address (send only the fields to change)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="street", type="string"),
 *         @OA\Property(property="city", type="string"),
 *         @OA\Property(property="postal_code", type="string"),
 *         @OA\Property(property="country", type="string"),
 *         @OA\Property(property="is_default", type="boolean", example=true)
 *     )),
 *     @OA\Response(response=200, description="The updated address"),
 *     @OA\Response(response=404, description="Address not found")
 * )
 */
Flight::route('PUT /addresses/@id:[0-9]+', function ($id) {
    Flight::json(Flight::addressService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/addresses/{id}", tags={"addresses"}, summary="Delete an address (old orders keep their data, the link becomes empty)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Address not found")
 * )
 */
Flight::route('DELETE /addresses/@id:[0-9]+', function ($id) {
    Flight::addressService()->delete((int) $id);
    Flight::json(['message' => "Address $id deleted"]);
});

/**
 * @OA\Get(
 *     path="/addresses", tags={"addresses"}, summary="List every address (all users)",
 *     @OA\Response(response=200, description="Array of addresses")
 * )
 */
Flight::route('GET /addresses', function () {
    Flight::json(Flight::addressService()->getAll());
});
