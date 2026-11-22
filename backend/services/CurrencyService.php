<?php
// External API: converts product prices from EUR with live rates from Frankfurter
// (European Central Bank rates, https://frankfurter.dev, no API key needed).
// If the API is slow or down, the failure is logged and the client gets a friendly 503;
// the rest of the shop keeps working and prices stay in EUR.

require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../utils/Logger.php';

class CurrencyService
{
    // Currencies the shop shows. BAM is not in the ECB data, but it is fixed to the euro by law
    // (1 EUR = 1.95583 BAM), so it is worked out here instead of being asked from the API.
    private const LIVE = ['USD', 'GBP', 'CHF'];
    private const BAM_PER_EUR = 1.95583;
    private const TIMEOUT_SECONDS = 5;

    // GET /products/{id}/prices: the product's EUR price in every supported currency.
    public function getProductPrices(int $productId): array
    {
        $product = (new ProductDao())->getById($productId);
        if ($product === null) {
            throw new ApiException("Product $productId not found", 404);
        }

        $live = $this->getRates();
        $rates = $live['rates'] + ['BAM' => self::BAM_PER_EUR];

        $price = (float) $product['price'];
        $prices = [];
        foreach ($rates as $currency => $rate) {
            $prices[$currency] = round($price * $rate, 2);
        }

        return [
            'product_id' => $productId,
            'base'       => 'EUR',
            'price'      => $price,
            'date'       => $live['date'],
            'prices'     => $prices,
        ];
    }

    // Asks Frankfurter for today's rates. Returns ['date' => ..., 'rates' => ['USD' => 1.09, ...]].
    private function getRates(): array
    {
        $url = rtrim(EXCHANGE_API_URL, '/') . '/latest?base=EUR&symbols=' . implode(',', self::LIVE);
        $started = microtime(true);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
        ]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        $ms = (int) round((microtime(true) - $started) * 1000);
        $data = is_string($body) ? json_decode($body, true) : null;

        if ($body === false || $status !== 200 || !isset($data['rates'], $data['date'])) {
            $reason = $body === false ? $curlError : "HTTP $status";
            Logger::error("External API failure (Frankfurter, {$ms}ms): $reason");
            throw new ApiException('Live exchange rates are not available right now. Prices are shown in EUR.', 503);
        }

        Logger::info("External API call (Frankfurter) 200 {$ms}ms");
        return ['date' => $data['date'], 'rates' => $data['rates']];
    }
}
