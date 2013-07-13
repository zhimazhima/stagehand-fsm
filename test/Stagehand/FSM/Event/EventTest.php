<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2006-2007, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM\Event;

use Stagehand\FSM\StateMachine\StateMachine;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getsTheId()
    {
        $event = new Event('foo');
        $this->assertEquals('foo', $event->getEventID());
    }

    /**
     * @test
     */
    public function setsTheAction()
    {
        $action = function () {};
        $event = new Event('foo');
        $event->setAction($action);
        $this->assertSame($action, $event->getAction());
    }

    /**
     * @test
     */
    public function setsTheGuard()
    {
        $guard = function () {};
        $event = new Event('foo');
        $event->setGuard($guard);
        $this->assertSame($guard, $event->getGuard());
    }

    /**
     * @test
     */
    public function evaluatesTheGuard()
    {
        $stateMachine = new StateMachine('bar');
        $payload = new \stdClass();
        $payload->name = 'baz';
        $stateMachine->setPayload($payload);
        $event = new Event('foo');
        $event->setGuard(function (EventInterface $event, $payload, StateMachine $stateMachine) { return true; });
        $this->assertTrue($event->evaluateGuard($stateMachine));
        $event->setGuard(function (EventInterface $event, $payload, StateMachine $stateMachine) { return false; });
        $this->assertFalse($event->evaluateGuard($stateMachine));
        $test = $this;
        $event->setGuard(function (EventInterface $event, $payload, StateMachine $stateMachine) use ($test) {
            $test->assertEquals('bar', $stateMachine->getStateMachineID());
            $test->assertEquals('foo', $event->getEventID());
            $test->assertEquals('baz', $payload->name);

            return true;
        });
        $this->assertTrue($event->evaluateGuard($stateMachine));
    }

    /**
     * @test
     */
    public function invokesTheAction()
    {
        $barInvoked = false;
        $stateMachine = new StateMachine('bar');
        $payload = new \stdClass();
        $payload->name = 'baz';
        $stateMachine->setPayload($payload);
        $event = new Event('foo');
        $event->setAction(function (EventInterface $event, $payload, StateMachine $stateMachine) use (&$barInvoked) {
            $barInvoked = true;
        });
        $event->invokeAction($stateMachine);
        $this->assertTrue($barInvoked);
        $test = $this;
        $event->setAction(function (EventInterface $event, $payload, StateMachine $stateMachine) use ($test) {
            $test->assertEquals('bar', $stateMachine->getStateMachineID());
            $test->assertEquals('foo', $event->getEventID());
            $test->assertEquals('baz', $payload->name);

            return true;
        });
        $event->invokeAction($stateMachine);
    }

    /**
     * @test
     * @since Method available since Release 2.0.0
     */
    public function checksWhetherAnEventIsProtectedOrNot()
    {
        $this->assertTrue(Event::isProtectedEvent(EventInterface::EVENT_ENTRY));
        $this->assertTrue(Event::isProtectedEvent(EventInterface::EVENT_EXIT));
        $this->assertTrue(Event::isProtectedEvent(EventInterface::EVENT_START));
        $this->assertTrue(Event::isProtectedEvent(EventInterface::EVENT_END));
        $this->assertTrue(Event::isProtectedEvent(EventInterface::EVENT_DO));
        $this->assertFalse(Event::isProtectedEvent('foo'));
    }

}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */