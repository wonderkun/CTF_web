<?php
namespace Aura\Intl;

class IntlFormatterTest extends BasicFormatterTest
{
    protected function newFormatter()
    {
        return new IntlFormatter;
    }

    public function setUp()
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('This test is skipped if the Intl Extension is not loaded.');
        }
    }

    public function testIntlVersion()
    {
        $this->setExpectedException('Aura\Intl\Exception\IcuVersionTooLow');
        $formatter = new IntlFormatter('4.7');
    }

    /**
     * This test fails on PHP 5.4.4
     * The return value expected is No pages , but returns false
     *
     * var_dump( msgfmt_get_error_message($fmt) );
     * U_ZERO_ERROR
     *
     * It seems $fmt = msgfmt_create($locale, $string); is not creating with
     * this string , so the msgfmt_format() throws expects parameter 1 to be
     * MessageFormatter, null given error.
     */
    public function testFormat_plural()
    {
        $formatter = $this->newFormatter();
        $locale = 'en_US';
        $string = '{pages,plural,'
                . '=0{No pages.}'
                . '=1{One page only.}'
                . 'other{Page {page} of # pages.}'
                . '}';

        $tokens_values = ['page' => 0, 'pages' => 0];
        $expect = 'No pages.';
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);

        $tokens_values = ['page' => 1, 'pages' => 1];
        $expect = 'One page only.';
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);

        $tokens_values = ['page' => 1, 'pages' => 2];
        $expect = 'Page 1 of 2 pages.';
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);
    }

    /**
     * @dataProvider provide_testFormat_select
     */
    public function testFormat_select($tokens_values, $expect)
    {
        $locale = 'en_US';
        $string = "
            {gender, select,
                female {
                    {count, plural, offset:1
                        =0 {{from} does not give a party.}
                        =1 {{from} invites {to} to her party.}
                        =2 {{from} invites {to} and one other person to her party.}
                        other {{from} invites {to} as one of the # people invited to her party.}
                    }
                }
                male {
                    {count, plural, offset:1
                        =0 {{from} does not give a party.}
                        =1 {{from} invites {to} to his party.}
                        =2 {{from} invites {to} and one other person to his party.}
                        other {{from} invites {to} as one of the # other people invited to his party.}
                    }
                }
                other {
                    {count, plural, offset:1
                        =0 {{from} does not give a party.}
                        =1 {{from} invites {to} to their party.}
                        =2 {{from} invites {to} and one other person to their party.}
                        other {{from} invites {to} as one of the # other people invited to their party.}
                    }
                }
            }";

        $formatter = $this->newFormatter();
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame(trim($expect), trim($actual));
    }

    public function provide_testFormat_select()
    {
        return [
            [array('gender' => 'female', 'count' => 0,  'from' => 'Alice', 'to' => 'Bob'), 'Alice does not give a party.'],
            [array('gender' => 'male',   'count' => 1,  'from' => 'Bob', 'to' => 'Alice'), 'Bob invites Alice to his party.'],
            [array('gender' => 'none',   'count' => 2,  'from' => 'Alice', 'to' => 'Bob'), 'Alice invites Bob and one other person to their party.'],
            [array('gender' => 'female', 'count' => 27, 'from' => 'Alice', 'to' => 'Bob'), 'Alice invites Bob as one of the 26 people invited to her party.'],
        ];
    }

    public function testFormat_cannotInstantiateFormatter()
    {
        $locale = 'en_US';
        // uses {count} instead of #, which should fail
        $string = "
            {gender, select,
                female {
                    {count, plural, offset:1
                        =0 {{from} does not give a party.}
                        =1 {{from} invites {to} to her party.}
                        =2 {{from} invites {to} and one other person to her party.}
                        other {{from} invites {to} as one of the {count} people invited to her party.}
                    }
                }
                male {
                    {count, plural, offset:1
                        =0 {{from} does not give a party.}
                        =1 {{from} invites {to} to his party.}
                        =2 {{from} invites {to} and one other person to his party.}
                        other {{from} invites {to} as one of the {count} other people invited to his party.}
                    }
                }
                other {
                    {count, plural, offset:1
                        =0 {{from} does not give a party.}
                        =1 {{from} invites {to} to their party.}
                        =2 {{from} invites {to} and one other person to their party.}
                        other {{from} invites {to} as one of the {count} other people invited to their party.}
                    }
                }
            }";
        $formatter = $this->newFormatter();
        $this->setExpectedException('Aura\Intl\Exception\CannotFormat');
        $actual = $formatter->format($locale, $string, array('gender' => 'female', 'count' => 5,  'from' => 'Alice', 'to' => 'Bob'));
    }

    // @todo MAKE IT SO THAT WE CHECK FOR TOKENS IN THE ARRAY
    public function testFormat_cannotFormat()
    {
        $locale = 'en_US';
        $string = 'Hello {foo}';
        $tokens_values = ['bar' => 'baz']; // no 'foo' token
        $formatter = $this->newFormatter();

        $expect = 'Hello {foo}';
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);
    }

    /**
     * @dataProvider provide_testIssue6
     */
    public function testIssue6($tokens_values, $expect)
    {
        $locale = 'en_US';
        $string = '{gender, select, female{{name} is {gender} and she report bugs!} male{{name} is {gender} and he report bugs!} other{{name} report bugs!}}';
        $formatter = $this->newFormatter();
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);
    }

    public function testEmptyStringThrowsException()
    {
        $locale = 'en_US';
        $string = '';
        $formatter = $this->newFormatter();
        if (! defined('HHVM_VERSION')) {
            $this->setExpectedException('Aura\Intl\Exception\CannotInstantiateFormatter');
        }
        $actual = $formatter->format($locale, $string, []);
        if (defined('HHVM_VERSION')) {
            // HHVM will instantiate Formatter even if empty string is passed.
            $expect = '';
            $this->assertSame($expect, $actual);
        }
    }

    public function provide_testIssue6()
    {
        return [
            [array('gender' => 'female', 'name' => 'Alice'), 'Alice is female and she report bugs!'],
            [array('gender' => 'male', 'name' => 'Alexander' ), 'Alexander is male and he report bugs!'],
            [array('gender' => '', 'name' => 'Unknown'), 'Unknown report bugs!'],
        ];
    }
}
