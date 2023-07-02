<?php
/**
     * @OA\Post(
     *     path="/market/public/api/deletepurchase/{id}",
     *     tags={"deletepurchase"},
     *     summary="Delete Purchase",
     *     operationId="deletepurchase",
     *     @OA\Response(
     *         response=400,
     *         description="Purchase Failed"
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Purchase successful"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="Purchase ID",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function deletepurchase(Request $request)
    {
       if (Purchase::where("id", $request->id)->exists()) {

            $purchase = Purchase::find($request->id);
            $purchase->delete();

            return response()->json([
                "status" => 1,
                "message" => "Purchase with Purchase ID ".$purchase->id." has been deleted",
                "data" => $purchase
            ]);
            } else {

                return response()->json([
                    "status" => 0,
                    "message" => "Purchase not found"
                ], 404);
            }        
    }


?>