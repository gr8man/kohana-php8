<?php
declare(strict_types=1);

namespace Kohana\Tests;

use \DB;
use \Database_Query_Builder_Select;

class DatabaseTest extends BaseTestCase
{
    public function test_query_builder_select(): void
    {
        $query = DB::select('id', 'username')
            ->from('users')
            ->where('status', '=', 'active')
            ->limit(10);
        
        $this->assertInstanceOf(Database_Query_Builder_Select::class, $query);
    }

    public function test_db_expr(): void
    {
        $expr = DB::expr('COUNT(*)');
        $this->assertEquals('COUNT(*)', (string) $expr);
    }
}
