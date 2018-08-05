<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookingRequest;
use App\Http\Controllers\Controller;
use App\Booking;
use App\User;
use App\Association;
use App\Item;
use App\BookingLine;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Booking::all();

        if($bookings){
            foreach ($bookings as $booking) {

                /*Requêtes pour les associations à changer avec Portail des assos*/
                $booking->owner = Association::find($booking->owner);
                $booking->booker = Association::find($booking->booker);

                /*Requêtes pour les utilisateurs à changer avec Portail des assos*/
                $booking->user = User::find($booking->user);

                /*Gestion des réceptions de caution*/
                if($booking->cautionReceived)
                    $booking->cautionReceived = "Oui";
                else
                    $booking->cautionReceived = "Non";

                /*Gestion des status*/
                switch ($booking->status) {
                    case '1':
                        $booking->status = "En cours";
                        break;
                    
                    default:
                        $booking->status = "";
                        break;
                }
            }

            return response()->json($bookings, 200);
        }
        else{
            return response()->json(["message" => "Impossible de trouver les réservations"], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BookingRequest $request)
    {
        $booking = Booking::create($request->all());

        if($booking)
        {
            return response()->json($booking, 200);
        }
        else
        {
            return response()->json(["message" => "Impossible de créer la réservation"], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $booking = Booking::find($id);    

        if($booking){
            //Récupération des informations liées à la réservation
            $booking->bookinglines = $booking->bookinglines()->get();
            foreach ($booking->bookinglines as $bookingline) {
                $bookingline->item = Item::find($bookingline->item);
            }

            /*Requêtes pour les associations à changer avec Portail des assos*/
            $booking->owner = Association::find($booking->owner);
            $booking->booker = Association::find($booking->booker);

            /*Requêtes pour les utilisateurs à changer avec Portail des assos*/
            $booking->user = User::find($booking->user);

            /*Gestion des réceptions de caution*/
                if($booking->cautionReceived)
                    $booking->cautionReceived = "Oui";
                else
                    $booking->cautionReceived = "Non";

                /*Gestion des status*/
                switch ($booking->status) {
                    case '1':
                        $booking->status = "En cours";
                        break;
                    
                    default:
                        $booking->status = "";
                        break;
                }

            return response()->json($booking, 200);
        }
        else
            return response()->json(["message" => "Impossible de trouver la réservation"], 500);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(BookingRequest $request, $id)
    {
        $booking = Booking::find($id);
        if($booking){
            $value = $booking->update($request->input());
            if($value)
                return response()->json($value, 201);
            return response()->json(['message'=>'An error ocured'],500);
        }
        return response()->json(["message" => "Impossible de trouver la réservation"], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $booking = Booking::find($id);

        if ($booking)
        {
            $booking->delete();
            return response()->json([], 200);
        }
        else
            return response()->json(["message" => "Impossible de trouver la réservation"], 500);
    }

    public function calculCaution(Request $request){
        $caution = 0;
        foreach ($request->items as $item) {
            $caution+=Item::find($item['id'])->caution*$item['quantity'];
        }
        return($caution);
    }

    public function indexAssociation($asso_id){

        $bookings = [
            /* Réservation où l'association est propriétaire */
            "ownerBookings"     =>  Booking::all()->where('owner', $asso_id),
            /* Réservation demandée par l'association */
            "bookerBookings"    =>  Booking::all()->where('booker', $asso_id),
        ];

        if($bookings['ownerBookings']){
            foreach ($bookings['ownerBookings'] as $booking) {

                $booking->booker = Association::find($booking->booker);
                
                $booking->user = User::find($booking->user);

                /*Gestion des réceptions de caution*/
                if($booking->cautionReceived)
                    $booking->cautionReceived = "Oui";
                else
                    $booking->cautionReceived = "Non";
            }
                }

        if($bookings['bookerBookings']){
            foreach ($bookings['bookerBookings'] as $booking) {

                $booking->booker = Association::find($booking->owner);

                /*Requêtes pour les utilisateurs à changer avec Portail des assos*/
                $booking->user = User::find($booking->user);

                /*Gestion des réceptions de caution*/
                if($booking->cautionReceived)
                    $booking->cautionReceived = "Oui";
                else
                    $booking->cautionReceived = "Non";

                /* Retrait de la caution que l'association demandeuse ne doit pas voir */
                $booking->offsetUnset('caution');


            }
        

            return response()->json($bookings, 200);
        }
        
    }
}
