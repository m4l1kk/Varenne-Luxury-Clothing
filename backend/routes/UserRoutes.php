<?php
// Routes only read the request and call the service; validation lives in UserService.

/**
 * @OA\Get(
 *     path="/users", tags={"users"}, summary="List users (password hashes are never returned)",
 *     @OA\Parameter(name="role", in="query", required=false, @OA\Schema(type="string", enum={"customer", "admin"})),
 *     @OA\Response(response=200, description="Array of users"),
 *     @OA\Response(response=400, description="Unknown role")
 * )
 */
Flight::route('GET /users', function () {
    $role = Flight::request()->query['role'];
    Flight::json($role ? Flight::userService()->getByRole($role) : Flight::userService()->getAll());
});

/**
 * @OA\Get(
 *     path="/users/{id}", tags={"users"}, summary="Get one user",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\Response(response=200, description="The user"),
 *     @OA\Response(response=404, description="User not found")
 * )
 */
Flight::route('GET /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::userService()->getById((int) $id));
});

/**
 * @OA\Post(
 *     path="/users", tags={"users"}, summary="Create a user (the password is hashed before it is saved)",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"first_name", "last_name", "email", "password"},
 *         @OA\Property(property="first_name", type="string", example="Lejla"),
 *         @OA\Property(property="last_name", type="string", example="Begic"),
 *         @OA\Property(property="email", type="string", example="lejla@example.com"),
 *         @OA\Property(property="password", type="string", example="Customer123!"),
 *         @OA\Property(property="phone", type="string", example="+387 61 555 666"),
 *         @OA\Property(property="role", type="string", enum={"customer", "admin"}, example="customer")
 *     )),
 *     @OA\Response(response=201, description="The created user"),
 *     @OA\Response(response=400, description="Missing or invalid field"),
 *     @OA\Response(response=409, description="Email already used")
 * )
 */
Flight::route('POST /users', function () {
    Flight::json(Flight::userService()->add(Flight::request()->data->getData()), 201);
});

/**
 * @OA\Put(
 *     path="/users/{id}", tags={"users"}, summary="Update a user (send only the fields to change)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=2)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="first_name", type="string"),
 *         @OA\Property(property="last_name", type="string"),
 *         @OA\Property(property="email", type="string"),
 *         @OA\Property(property="password", type="string"),
 *         @OA\Property(property="phone", type="string", example="+387 61 999 000"),
 *         @OA\Property(property="role", type="string", enum={"customer", "admin"})
 *     )),
 *     @OA\Response(response=200, description="The updated user"),
 *     @OA\Response(response=400, description="Invalid field"),
 *     @OA\Response(response=404, description="User not found"),
 *     @OA\Response(response=409, description="Email already used")
 * )
 */
Flight::route('PUT /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::userService()->update((int) $id, Flight::request()->data->getData()));
});

/**
 * @OA\Delete(
 *     path="/users/{id}", tags={"users"}, summary="Delete a user (refused while the user has orders)",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="User not found"),
 *     @OA\Response(response=409, description="The user has orders")
 * )
 */
Flight::route('DELETE /users/@id:[0-9]+', function ($id) {
    Flight::userService()->delete((int) $id);
    Flight::json(['message' => "User $id deleted"]);
});
