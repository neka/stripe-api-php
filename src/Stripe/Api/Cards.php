<?php
/**
 * User: Joe Linn
 * Date: 3/20/2014
 * Time: 2:43 PM
 */

namespace Stripe\Api;


use Stripe\Exception\CardDeclinedException;
use Stripe\Exception\IncorrectNumberException;
use Stripe\Exception\InvalidCvcException;
use Stripe\Exception\InvalidExpiryMonthException;
use Stripe\Exception\InvalidExpiryYearException;
use Stripe\Request\Cards\CreateCardRequest;
use Stripe\Request\Cards\UpdateCardRequest;
use Stripe\Response\Cards\CardResponse;
use Stripe\Response\Cards\ListCardsResponse;
use Stripe\Response\DeleteResponse;
use Stripe\StripeException;

class Cards extends AbstractApi
{
    /**
     * @param string $customerId
     * @param string $token
     * @throws \Stripe\StripeException
     * @return CardResponse
     * @link https://stripe.com/docs/api/curl#create_card
     */
    public function createCardFromToken($customerId, $token)
    {
        try {
            $response = $this->client->request('POST', $this->buildUrl($customerId), 'Stripe\Response\Cards\CardResponse', array('card' => $token));
        } catch (StripeException $e) {
            throw $this->handleError($e);
        }
        return $response;
    }

    /**
     * @param string $customerId
     * @param CreateCardRequest $request
     * @throws \Exception
     * @throws \Stripe\StripeException
     * @return CardResponse
     * @link https://stripe.com/docs/api/curl#create_card
     */
    public function createCard($customerId, CreateCardRequest $request)
    {
        try {
            $response = $this->client->request('POST', $this->buildUrl($customerId), 'Stripe\Response\Cards\CardResponse', array('card' => $request));
        } catch (StripeException $e) {
            throw $this->handleError($e);
        }
        return $response;
    }

    /**
     * @param string $customerId
     * @param string $cardId
     * @param UpdateCardRequest $request
     * @return CardResponse
     * @throws \Stripe\StripeException
     * @link https://stripe.com/docs/api/curl#update_card
     */
    public function updateCard($customerId, $cardId, UpdateCardRequest $request)
    {
        try {
            $response = $this->client->request('POST', $this->buildUrl($customerId, $cardId), 'Stripe\Response\Cards\CardResponse', $request);
        } catch (StripeException $e) {
            throw $this->handleError($e);
        }
        return $response;
    }

    /**
     * @param string $customerId
     * @param string $cardId
     * @return CardResponse
     * @link https://stripe.com/docs/api/curl#retrieve_card
     */
    public function getCard($customerId, $cardId)
    {
        return $this->client->request('GET', $this->buildUrl($customerId, $cardId), 'Stripe\Response\Cards\CardResponse');
    }

    /**
     * @param string $customerId
     * @param string $cardId
     * @return DeleteResponse
     */
    public function deleteCard($customerId, $cardId)
    {
        return $this->client->request('DELETE', $this->buildUrl($customerId, $cardId), 'Stripe\Response\DeleteResponse');
    }

    /**
     * @param string $customerId
     * @param int $count
     * @param int $offset
     * @return ListCardsResponse
     * @link https://stripe.com/docs/api/curl#list_cards
     */
    public function listCards($customerId, $count = 10, $offset = 0)
    {
        return $this->client->request('GET', $this->buildUrl($customerId), 'Stripe\Response\Cards\ListCardsResponse', null, array('count' => $count, 'offset' => $offset));
    }

    /**
     * Construct a card request url
     * @param string $customerId
     * @param string $cardId
     * @return string
     */
    protected function buildUrl($customerId, $cardId = null)
    {
        $url = 'customers/' . $customerId . '/cards';
        if (!is_null($cardId)) {
            $url .= '/' . $cardId;
        }
        return $url;
    }

    /**
     * @param StripeException $e
     * @return CardDeclinedException|IncorrectNumberException|InvalidCvcException|InvalidExpiryMonthException|InvalidExpiryYearException|StripeException
     */
    protected function handleError(StripeException $e)
    {
        if ($e->getType() == "card_error") {
            switch ($e->getCardErrorCode()) {
                case "incorrect_number":
                    return new IncorrectNumberException($e);
                case "card_declined":
                    return new CardDeclinedException($e);
                case "invalid_expiry_month":
                    return new InvalidExpiryMonthException($e);
                case "invalid_expiry_year":
                    return new InvalidExpiryYearException($e);
                case "invalid_cvc":
                    return new InvalidCvcException($e);
                default:
                    return $e;
            }
        }
        return $e;
    }
} 