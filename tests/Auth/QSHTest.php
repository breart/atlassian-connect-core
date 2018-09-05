<?php

namespace AtlassianConnectCore\Tests\Clients;

use AtlassianConnectCore\Http\Auth\QSH;

class QSHTest extends \AtlassianConnectCore\Tests\TestCase
{
    public function testCreate()
    {
        $this->assertQSH('GET', 'https://addon.example.com', 'c88caad15a1c1a900b8ac08aa9686f4e8184539bea1deda36e2f649430df3239');
        $this->assertQSH('GET', 'https://example.atlassian.net/rest/api/2/issue/', '86b803b109338286ccafd963279ad7c7c8aa6b62397acb179faf24a5323b218f');
        // $this->assertQSH('GET', 'https://addon.example.com/wiki/title&description', 'b59dff3220c9473597d76ab6ea943b8e84e2a490e537bb4451ce57dc1bc1cd06');
    }

    public function testCanonicalUri()
    {
        $this->assertCanonicalUri('https://addon.example.com/', '/');
        $this->assertCanonicalUri('https://addon.example.com/wiki/issue', '/issue');
        $this->assertCanonicalUri('https://addon.example.com/wiki/title&description', '/title%26description');
        $this->assertCanonicalUri('https://example.atlassian.net/rest/api/2/issue/', '/rest/api/2/issue');
    }

    public function testCanonicalQuery()
    {
        // Ignore the jwt parameter
        $this->assertCanonicalQuery('jwt=ABC.DEF.GHI', '');
        $this->assertCanonicalQuery('expand=names&jwt=ABC.DEF.GHI', 'expand=names');

        // // URL-encode parameter keys
        $this->assertCanonicalQuery('enabled', 'enabled');
        $this->assertCanonicalQuery('some+spaces+in+this+parameter', 'some%20spaces%20in%20this%20parameter');
        $this->assertCanonicalQuery('connect*', 'connect%2A');
        $this->assertCanonicalQuery('1+%2B+1+equals+3', '1%20%2B%201%20equals%203');
        $this->assertCanonicalQuery('in+%7E3+days', 'in%20~3%20days');

        // URL-encode parameter values
        // For each parameter concatenate its URL-encoded name and its URL-encoded value with the = character.
        $this->assertCanonicalQuery('param=value', 'param=value');
        $this->assertCanonicalQuery('param=some+spaces+in+this+parameter', 'param=some%20spaces%20in%20this%20parameter');
        $this->assertCanonicalQuery('query=connect*', 'query=connect%2A');
        $this->assertCanonicalQuery('a=b&', 'a=b');
        $this->assertCanonicalQuery('director=%E5%AE%AE%E5%B4%8E%20%E9%A7%BF', 'director=%E5%AE%AE%E5%B4%8E%20%E9%A7%BF');

        // URL-encoding is upper case
        $this->assertCanonicalQuery('director=%e5%ae%ae%e5%b4%8e%20%e9%a7%bf', 'director=%E5%AE%AE%E5%B4%8E%20%E9%A7%BF');

        // Sort query parameter keys
        $this->assertCanonicalQuery('a=x&b=y', 'a=x&b=y');
        $this->assertCanonicalQuery('a10=1&a1=2&b1=3&b10=4', 'a1=2&a10=1&b1=3&b10=4');
        $this->assertCanonicalQuery('A=A&a=a&b=b&B=B', 'A=A&B=B&a=a&b=b');

        // Sort query parameter value lists
        // In the case of repeated parameters, concatenate sorted values with a , character.
        $this->assertCanonicalQuery('ids=-1&ids=1&ids=10&ids=2&ids=20', 'ids=-1,1,10,2,20');
        $this->assertCanonicalQuery('ids=.1&ids=.2&ids=%3A1&ids=%3A2', 'ids=.1,.2,%3A1,%3A2');
        $this->assertCanonicalQuery('ids=10%2C2%2C20%2C1', 'ids=10%2C2%2C20%2C1');
        $this->assertCanonicalQuery('tuples=1%2C2%2C3&tuples=6%2C5%2C4&tuples=7%2C9%2C8', 'tuples=1%2C2%2C3,6%2C5%2C4,7%2C9%2C8');
        $this->assertCanonicalQuery('chars=%E5%AE%AE&chars=%E5%B4%8E&chars=%E9%A7%BF', 'chars=%E5%AE%AE,%E5%B4%8E,%E9%A7%BF');
        $this->assertCanonicalQuery('c=&c=+&c=%2520&c=%2B', 'c=,%20,%2520,%2B');
        $this->assertCanonicalQuery('a=x1&a=x10&b=y1&b=y10', 'a=x1,x10&b=y1,y10');
        $this->assertCanonicalQuery('a=another+one&a=one+string&b=and+yet+more&b=more+here', 'a=another%20one,one%20string&b=and%20yet%20more,more%20here');
        $this->assertCanonicalQuery('a=1%2C2%2C3&a=4%2C5%2C6&b=a%2Cb%2Cc&b=d%2Ce%2Cf', 'a=1%2C2%2C3,4%2C5%2C6&b=a%2Cb%2Cc,d%2Ce%2Cf');
    }

    /**
     * Assert generated canonical URI with expected.
     *
     * @param string $url
     * @param string $expected
     */
    protected function assertCanonicalUri(string $url, string $expected)
    {
        $qsh = new QSH($url, 'GET');

        static::assertSame($expected, $qsh->canonicalUri());
    }

    /**
     * Assert generated canonical query string with expected.
     *
     * @param string $query
     * @param string $expected
     */
    protected function assertCanonicalQuery(string $query, string $expected)
    {
        $qsh = new QSH('https://example.atlassian.net/?' . $query, 'GET');

        static::assertSame($expected, $qsh->canonicalQuery());
    }

    /**
     * Assert generated QSH with expected.
     *
     * @param string $method
     * @param string $url
     * @param string $expected
     */
    protected function assertQSH(string $method, string $url, string $expected)
    {
        $qsh = new QSH($url, $method);

        static::assertSame($expected, $qsh->create());
    }
}