<?php

namespace App\Modules\winkelmandje\services;

use Illuminate\Support\Facades\Validator;

class KortingService
{
    public function winkelmandje($request) {

        $validated = $this->validator($request->all());

        if ($validated->fails()) {
            return response()->json($validated);
        }

        $winkelmandje = $request->all();
        $producten = $winkelmandje['winkelmandje'];
        $totaalprijs = 0;

        foreach ($producten as &$product) {
            if ($this->bepaalGratisProduct($product['productId'], $product['aantal'])) {
                $product['aantal'] = $product['aantal'] - 1;
            }

            if ($this->bepaalKorting($product['productId'])) {
                $product['eenheidsprijs'] = $product['eenheidsprijs'] - ($product['eenheidsprijs'] / 100)*20;
            }

            $totaalprijs += $this->berekenTotaalPrijs($product);
        }

        return $this->jsonBuilder($producten, $totaalprijs);
    }

    private function jsonBuilder($producten, $totaalprijs){
        return json_encode(
            array(
                "winkelmandje" => $producten,
                "totaalprijs" => $totaalprijs
            ));
    }

    private function berekenTotaalPrijs($product) {
        $prijsProductZonderBTW = ($product['eenheidsprijs'] * $product['aantal']);
        $BTW = (($product['eenheidsprijs'] * $product['aantal']) / 100) * $product['BTW'];
        return $prijsProductZonderBTW + $BTW;
    }

    /**
     * De 2 onderstaande functies halen normaal het product uit de database om te kijken of deze 2 plus 1 gratis is of ze in korting staan.
     * Omdat ik geen database gebruikt heb ik dit gedaan met een match.
    **/
    private function bepaalGratisProduct($productId, $aantal) {
        if ( match ($productId) {
            7, 5, 2 => true,
            default => false,
        })
        {
            if ($aantal  >= 3 ){
                return true;
            }
        } else return false;
    }

    private function bepaalKorting($productId) {
        return match ($productId) {
            1, 3, 5, => true,
            default => false,
        };
    }
    /**
    **/

    protected function validator(array $data) {
        return Validator::make($data,
            [
                'winkelmandje.*.productId' => ['required', 'int'],
                'winkelmandje.*.productNaam' => ['required', 'string'],
                'winkelmandje.*.aantal' => ['required', 'int'],
                'winkelmandje.*.eenheidsprijs' => ['required', 'int'],
                'winkelmandje.*.BTW' => ['required', 'int'],

            ],
            [
                'winkelmandje.*.productId.required' => 'ProductId moet aanwezig zijn.',
                'winkelmandje.*.productNaam' => 'Product naam moet aanwezig zijn',
                'winkelmandje.*.aantal' => 'Aantal naam moet aanwezig zijn',
                'winkelmandje.*.eenheidsprijs' => 'Eenheidsprijs naam moet aanwezig zijn',
                'winkelmandje.*.BTW' => 'BTW naam moet aanwezig zijn',
            ]
        );
    }
}

/**
winkelmandje: {
    "product_1": {
        "productId":    "1",
        "productNaam":  "Koptelefoon",
        "aantal":       "3",
        "eenheidsprijs":"150",
        "BTW":          "21",
    },
    "product_2": {
        "productId":    "5",
        "productNaam":  "Radio",
        "aantal":       "6",
        "eenheidsprijs":"50",
        "BTW":          "21",
    },
}
 **/
