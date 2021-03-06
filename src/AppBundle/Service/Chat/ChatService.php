<?php
/**
 * Created by PhpStorm.
 * User: rassamakhinny
 * Date: 08.07.16
 * Time: 0:06
 */
namespace AppBundle\Service\Chat;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;                                                                                                                                                                     

class ChatService implements TopicInterface
{
    public function getName()
    {
        return 'app.service.chat';
    }

    /**
     * This will receive any Subscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        $room = $request->getAttributes()->get('room');
        $userId = $request->getAttributes()->get('user_id');

        //this will broadcast the message to ALL subscribers of this topic.
        $topic->broadcast(['msg' => 'Новый пользователь зашел в комнату ' . $room . ' в личку к пользователю ' . $userId]);
    }

    /**
     * This will receive any UnSubscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        $room = $request->getAttributes()->get('room');
        $userId = $request->getAttributes()->get('user_id');
        //this will broadcast the message to ALL subscribers of this topic.
        $topic->broadcast(['msg' => 'Новый пользователь вышел из комнаты ' . $room . ' лички с пользователем ' . $userId]);
    }


    /**
     * This will receive any Publish requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param $event
     * @param array $exclude
     * @param array $eligible
     * @return mixed|void
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {

        $room = $request->getAttributes()->get('room');
        $userId = $request->getAttributes()->get('user_id');

        $topic->broadcast([
            'msg' => 'В комнату ' . $room . 'пользователю ' . $userId . ' поступило сообщение: ' . $event,
        ]);
    }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        

    public function onMessage(ConnectionInterface $from, $msg)
    {

    }
}