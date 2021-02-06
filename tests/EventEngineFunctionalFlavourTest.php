<?php
/**
 * This file is part of event-engine/php-engine.
 * (c) 2018-2021 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EventEngineTest;

use EventEngine\DocumentStore\DocumentStore;
use EventEngine\EventEngine;
use EventEngine\Messaging\MessageDispatcher;
use EventEngine\Messaging\MessageFactory;
use EventEngine\Persistence\AggregateStateStore;
use EventEngine\Runtime\Flavour;
use EventEngine\Runtime\FunctionalFlavour;
use EventEngineExample\FunctionalFlavour\Aggregate\UserDescription;
use EventEngineExample\FunctionalFlavour\Aggregate\UserState;
use EventEngineExample\FunctionalFlavour\Api\MessageDescription;
use EventEngineExample\FunctionalFlavour\ContextProvider\MatchingHobbiesProvider;
use EventEngineExample\FunctionalFlavour\ContextProvider\SocialPlatformProvider;
use EventEngineExample\FunctionalFlavour\ExampleFunctionalPort;
use EventEngineExample\FunctionalFlavour\PreProcessor\RegisterUserIfNotExists;
use EventEngineExample\FunctionalFlavour\ProcessManager\SendWelcomeEmail;
use EventEngineExample\FunctionalFlavour\Projector\RegisteredUsersProjector;
use EventEngineExample\FunctionalFlavour\Resolver\GetUserResolver;
use EventEngineExample\FunctionalFlavour\Resolver\GetUsersResolver;
use EventEngineExample\PrototypingFlavour\Aggregate\UserMetadataProvider;

abstract class EventEngineFunctionalFlavourTest extends EventEngineTestAbstract
{
    protected function loadEventEngineDescriptions(EventEngine $eventEngine)
    {
        $eventEngine->load(MessageDescription::class);
        $eventEngine->load(UserDescription::class);
    }

    protected function getFlavour(): Flavour
    {
        return new FunctionalFlavour(new ExampleFunctionalPort());
    }

    protected function getFlavourWithUserMetadataProvider(): Flavour
    {
        return new FunctionalFlavour(new ExampleFunctionalPort(), null, new UserMetadataProvider());
    }

    protected function getChangeUsernamePreProcessor(MessageFactory $messageFactory, AggregateStateStore $stateStore)
    {
        return new RegisterUserIfNotExists($messageFactory, $stateStore);
    }

    protected function getRegisteredUsersProjector(DocumentStore $documentStore)
    {
        return new RegisteredUsersProjector($documentStore);
    }

    protected function getUserRegisteredListener(MessageDispatcher $messageDispatcher)
    {
        return new SendWelcomeEmail($messageDispatcher);
    }

    protected function getUserResolver(array $cachedUserState)
    {
        return new GetUserResolver($cachedUserState);
    }

    protected function getSocialPlatformProvider()
    {
        return new SocialPlatformProvider();
    }

    protected function getMatchingHobbiesProvider()
    {
        return new MatchingHobbiesProvider();
    }

    protected function getUsersResolver(array $cachedUsers)
    {
        return new GetUsersResolver($cachedUsers);
    }

    protected function assertLoadedUserState($userState): void
    {
        self::assertInstanceOf(UserState::class, $userState);
        self::assertEquals('Tester', $userState->username);
    }
}
