<?php

use Clue\QDataStream\QVariant;
use Clue\QDataStream\Reader;
use Clue\QDataStream\Types;
use Clue\QDataStream\Writer;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testQString()
    {
        $in = 'hello';

        $writer = new Writer();
        $writer->writeQString($in);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals($in, $reader->readQString());
    }

    public function testQStringUnicode()
    {
        $in = 'hellö € 10';

        $writer = new Writer();
        $writer->writeQString($in);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals($in, $reader->readQString());
    }

    public function testQStringEmpty()
    {
        $writer = new Writer();
        $writer->writeQString('');

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals('', $reader->readQString());
    }

    public function testQStringNull()
    {
        $writer = new Writer();
        $writer->writeQString(null);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals(null, $reader->readQString());
    }

    public function testQByteArrayEmpty()
    {
        $writer = new Writer();
        $writer->writeQByteArray('');

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals('', $reader->readQByteArray());
    }

    public function testQByteArrayNull()
    {
        $writer = new Writer();
        $writer->writeQByteArray(null);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals(null, $reader->readQByteArray());
    }

    public function testQVariantAutoTypes()
    {
        $in = (object)array(
            'hello' => 'world',
            'bool' => true,
            'year' => 2015,
            'list' => array(
                'first',
                'second'
            )
        );

        $writer = new Writer();
        $writer->writeQVariant($in);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals($in, $reader->readQVariant());

        return new Reader($data);
    }

    public function testQVariantAutoTypesEncodesAssocArrayAsMapAndListAsList()
    {
        $in = array(
            'hello' => 'world',
            'bool' => true,
            'year' => 2015,
            'list' => array(
                'first',
                'second'
            )
        );

        $writer = new Writer();
        $writer->writeQVariant($in);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals($in, (array)$reader->readQVariant());
    }

    public function provideQVariantExplicitType()
    {
        return array(
            'char' => array(
                new QVariant(100, Types::TYPE_CHAR),
                100
            ),
            'uchar' => array(
                new QVariant(255, Types::TYPE_UCHAR),
                255
            ),
            'qchar' => array(
                new QVariant('ö', Types::TYPE_QCHAR),
                'ö'
            ),
            'qbytearray' => array(
                new QVariant('hi', Types::TYPE_QBYTE_ARRAY),
                'hi'
            ),
            'short' => array(
                new QVariant(30000, Types::TYPE_SHORT),
                30000
            )
        );
    }

    /**
     * @dataProvider provideQVariantExplicitType
     * @param QVariant $qvariant
     * @param mixed $expected
     */
    public function testQVariantExplicitType(QVariant $qvariant, $expected)
    {
        $writer = new Writer();
        $writer->writeQVariant($qvariant);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals($expected, $reader->readQVariant());
    }

    public function testQVariantListSomeExplicit()
    {
        $in = array(
            new QVariant(-10, Types::TYPE_CHAR),
            new QVariant(20, Types::TYPE_UINT),
            -300,
            new QVariant(array('hello', 'world'), Types::TYPE_QSTRING_LIST)
        );
        $expected = array(
            -10,
            20,
            -300,
            array('hello', 'world')
        );

        $writer = new Writer();
        $writer->writeQVariantList($in);

        $data = (string)$writer;

        $reader = new Reader($data);
        $this->assertEquals($expected, $reader->readQVariantList());
    }

    public function testQVariantMapSomeExplicit()
    {
        $in = (object)array(
            'id' => new QVariant(62000, Types::TYPE_USHORT),
            'name' => 'test'
        );
        $expected = (object)array(
            'id' => 62000,
            'name' => 'test'
        );

        $writer = new Writer();
        $writer->writeQVariantMap($in);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals($expected, $reader->readQVariantMap());
    }

    public function testQUserType()
    {
        $in = array(
            'id' => 62000,
            'name' => 'test'
        );

        $writer = new Writer(array(
            'user' => function ($data, Writer $writer) {
                $writer->writeUShort($data['id']);
                $writer->writeQString($data['name']);
            }
        ));
        $writer->writeQVariant(new QVariant($in, 'user'));

        $data = (string)$writer;
        $reader = new Reader($data, array(
            'user' => function (Reader $reader) {
                return array(
                    'id' => $reader->readUShort(),
                    'name' => $reader->readQString()
                );
            }
        ));

        $this->assertEquals($in, $reader->readQVariant());
    }

    public function testQStringList()
    {
        $writer = new Writer();
        $writer->writeQStringList(array('hello', 'world'));

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals(array('hello', 'world'), $reader->readQStringList());
    }

    public function testQCharMultipleUnicode()
    {
        $writer = new Writer();
        $writer->writeQChar('a');
        $writer->writeQChar('ä');

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals('a', $reader->readQChar());
        $this->assertEquals('ä', $reader->readQChar());
    }

    public function testShorts()
    {
        $writer = new Writer();
        $writer->writeShort(-100);
        $writer->writeUShort(60000);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals(-100, $reader->readShort());
        $this->assertEquals(60000, $reader->readUShort());
    }

    public function testChars()
    {
        $writer = new Writer();
        $writer->writeChar(-100);
        $writer->writeUChar(250);

        $data = (string)$writer;
        $reader = new Reader($data);

        $this->assertEquals(-100, $reader->readChar());
        $this->assertEquals(250, $reader->readUChar());
    }

    public function testReadQTimeNow()
    {
        date_default_timezone_set('UTC');

        $now = new \DateTime();

        $writer = new Writer();
        $writer->writeQTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQTime();
        $this->assertLessThan(1000, $now->diff($dt)->format('%u'));
    }

    public function testReadQTimeNowCorrectTimezone()
    {
        date_default_timezone_set('Europe/Berlin');

        $now = new \DateTime();

        $writer = new Writer();
        $writer->writeQTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQTime();

        $this->assertLessThan(1000, $now->diff($dt)->format('%u'));
        $this->assertEquals('Europe/Berlin', $dt->getTimezone()->getName());
    }

    public function testReadQTimeNotTodayCanNotReturnDayInPast()
    {
        date_default_timezone_set('UTC');

        $time = '2015-05-01 16:02:03';
        $now = new \DateTime($time);

        $writer = new Writer();
        $writer->writeQTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQTime();
        $this->assertNotEquals($now->format('U.u'), $dt->format('U.u'));
    }

    public function testReadQTimeSubSecond()
    {
        date_default_timezone_set('UTC');

        $time = '16:02:03.413';
        $now = new \DateTime($time);

        $writer = new Writer();
        $writer->writeQTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQTime();
        $this->assertEquals($now->format('U.u'), $dt->format('U.u'));
    }

    public function testReadQTimeMicrotimeWithMillisecondAccuracy()
    {
        date_default_timezone_set('UTC');

        $now = microtime(true);

        $writer = new Writer();
        $writer->writeQTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQTime();
        $this->assertEqualsDelta($now, $dt->format('U.u'), 0.001);
    }

    public function testReadQDateTimeNow()
    {
        date_default_timezone_set('UTC');

        $now = new \DateTime();

        $writer = new Writer();
        $writer->writeQDateTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertLessThan(1000, $now->diff($dt)->format('%u'));
    }

    public function testReadQVariantWithQDateTimeNow()
    {
        date_default_timezone_set('UTC');

        $now = new \DateTime();

        $writer = new Writer();
        $writer->writeQVariant($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQVariant();
        $this->assertLessThan(1000, $now->diff($dt)->format('%u'));
    }

    public function testReadQDateTimeNowWithCorrectTimezone()
    {
        date_default_timezone_set('Europe/Berlin');

        $now = new \DateTime();

        $writer = new Writer();
        $writer->writeQDateTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertLessThan(1000, $now->diff($dt)->format('%u'));

        $this->assertEquals('Europe/Berlin', $dt->getTimezone()->getName());
    }

    public function testReadQDateTimeWithDST()
    {
        date_default_timezone_set('Europe/Berlin');

        $now = '2015-09-22 09:45:12';
        $now = new \DateTime($now);

        $writer = new Writer();
        $writer->writeQDateTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertEquals($now, $dt);
    }

    public function testReadQDateTime()
    {
        date_default_timezone_set('UTC');

        $writer = new Writer();
        $writer->writeUInt(2457136); // day 2457136 - 2015-04-23
        $writer->writeUInt(50523000); // msec 50523000 - 14:02:03 UTC
        $writer->writeBool(true);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertEquals('2015-04-23 14:02:03', $dt->format('Y-m-d H:i:s'));

        $writer = new Writer();
        $writer->writeQDateTime($dt);

        $out = (string)$writer;

        $this->assertEquals($in, $out);
    }

    public function testReadQDateTimeNull()
    {
        date_default_timezone_set('UTC');

        $writer = new Writer();
        $writer->writeUInt(0);
        $writer->writeUInt(0xFFFFFFFF);
        $writer->writeBool(true);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertNull($dt);
    }

    public function testReadQDateTimeSubSecond()
    {
        date_default_timezone_set('UTC');

        $time = '16:02:03.413';
        $now = new \DateTime($time);

        $writer = new Writer();
        $writer->writeQDateTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertEquals($now->format('U.u'), $dt->format('U.u'));
    }

    public function testReadQDateTimeMicrotimeWithMillisecondAccuracy()
    {
        date_default_timezone_set('UTC');

        $now = microtime(true);

        $writer = new Writer();
        $writer->writeQDateTime($now);

        $in = (string)$writer;
        $reader = new Reader($in);

        $dt = $reader->readQDateTime();
        $this->assertEqualsDelta($now, $dt->format('U.u'), 0.001);
    }

    public function assertEqualsDelta($expected, $actual, $delta, $message = '')
    {
        if (method_exists($this, 'assertEqualsWithDelta')) {
            // PHPUnit 7.5+
            $this->assertEqualsWithDelta($expected, $actual, $delta, $message);
        } else {
            // legacy PHPUnit 4 - PHPUnit 7.4
            $this->assertEquals($expected, $actual, $message, $delta);
        }
    }
}
