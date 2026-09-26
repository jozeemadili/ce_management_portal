<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Reads the bulk member upload sheet as plain rows keyed by its heading row
 * ("First Name" -> first_name). Validation and saving live in
 * App\Services\MemberBulkImporter, so a file can be checked (preview) before
 * anything is written.
 */
class MembersImport implements WithHeadingRow
{
}
