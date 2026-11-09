<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberArea;
use App\Models\Record;
use App\Models\Record_List;
use App\Models\RecordPending;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HelpTakeoverTest extends TestCase
{
    use RefreshDatabase;

    private function member(int $id, string $nik): Member
    {
        MemberArea::create(['nik' => $nik, 'area' => 'sub_assy']);
        $m = new Member();
        $m->forceFill(['id' => $id, 'nik' => $nik]);

        return $m;
    }

    private function makeRecord(): Record
    {
        $type = Type::create(['Type' => '132']);
        $record = Record::create([
            'Id_User' => 1001, 'Sequence_No_Record' => 'S1', 'Production_Date_Record' => '2026-11-04',
            'Type' => '132', 'Area' => 'sub_assy', 'Time_Record' => now(),
        ]);
        foreach ([['A', 1], ['A', 2], ['B', 3], ['B', 4]] as [$box, $seq]) {
            Record_List::create([
                'Id_Record' => $record->Id_Record, 'Sequence_No' => $seq, 'Code_Part' => "P$seq", 'Name_Part' => "Part $seq",
                'Code_Rack' => "R$seq", 'Location_Rack' => "L$seq", 'Difference' => '-', 'Box' => $box, 'Qty' => 1, 'Mode' => 'manual', 'Area' => 'sub_assy',
            ]);
        }

        return $record;
    }

    public function test_takeover_only_moves_chosen_box_and_keeps_pending_active(): void
    {
        $record = $this->makeRecord();
        $owner = $this->member(1001, 'N1001');
        $taker = $this->member(2002, 'N2002');
        $pending = RecordPending::create(['Id_Record' => $record->Id_Record, 'Id_User' => 1001, 'Reason' => 'istirahat', 'Pending_At' => now()]);

        Auth::guard('member')->setUser($taker);
        $response = $this->post(route('member.record.takeover', $pending->id), ['boxes' => ['sub_assy|B']]);

        $this->assertSame([2002, 2002], Record_List::where('Box', 'B')->pluck('Assigned_To')->all());
        $this->assertSame([null, null], Record_List::where('Box', 'A')->pluck('Assigned_To')->all());
        $this->assertNull($pending->fresh()->Resumed_At);
        $this->assertSame('B', Record_List::where('Id_Record_List', $this->nextId($response))->value('Box'));
    }

    public function test_takeover_of_all_boxes_closes_pending(): void
    {
        $record = $this->makeRecord();
        $this->member(1001, 'N1001');
        $taker = $this->member(2002, 'N2002');
        $pending = RecordPending::create(['Id_Record' => $record->Id_Record, 'Id_User' => 1001, 'Reason' => 'x', 'Pending_At' => now()]);

        Auth::guard('member')->setUser($taker);
        $this->post(route('member.record.takeover', $pending->id), ['boxes' => ['sub_assy|A', 'sub_assy|B']]);

        $pending->refresh();
        $this->assertNotNull($pending->Resumed_At);
        $this->assertSame(2002, (int) $pending->Taken_By);
    }

    public function test_double_submit_keeps_first_time_record(): void
    {
        $record = $this->makeRecord();
        $owner = $this->member(1001, 'N1001');
        $part = Record_List::where('Id_Record', $record->Id_Record)->orderBy('Sequence_No')->first();

        Auth::guard('member')->setUser($owner);
        $this->post(route('member.record.update-part', $part->Id_Record_List), ['Code_Rack' => $part->Code_Rack]);
        $first = $part->fresh()->Time_Record;
        $this->assertNotNull($first);

        $this->travel(5)->minutes();
        $this->post(route('member.record.update-part', $part->Id_Record_List), ['Code_Rack' => $part->Code_Rack]);

        $this->assertEquals($first, $part->fresh()->Time_Record);
    }

    public function test_helper_takes_one_box_and_is_offered_next_free_box(): void
    {
        $record = $this->makeRecord();
        Record_List::create([
            'Id_Record' => $record->Id_Record, 'Sequence_No' => 5, 'Code_Part' => 'P5', 'Name_Part' => 'Part 5',
            'Code_Rack' => 'R5', 'Location_Rack' => 'L5', 'Difference' => '-', 'Box' => 'C', 'Qty' => 1, 'Mode' => 'manual', 'Area' => 'sub_assy',
        ]);
        $this->member(1001, 'N1001');
        $helper = $this->member(2002, 'N2002');
        Auth::guard('member')->setUser($helper);

        // Box A sedang dikerjakan pemilik; helper memilih satu box (B)
        $response = $this->post(route('member.record.help', $record->Id_Record), ['box' => 'sub_assy|B']);
        $this->assertSame([2002, 2002], Record_List::where('Box', 'B')->pluck('Assigned_To')->all());
        $this->assertSame([null], Record_List::where('Box', 'C')->pluck('Assigned_To')->all());
        $this->assertSame('B', Record_List::where('Id_Record_List', $this->nextId($response))->value('Box'));

        foreach (Record_List::where('Box', 'B')->orderBy('Sequence_No')->get() as $part) {
            $response = $this->post(route('member.record.update-part', $part->Id_Record_List), ['Code_Rack' => $part->Code_Rack]);
        }
        $response->assertSessionHas('help_next', fn ($n) => $n['record_id'] == $record->Id_Record
            && array_column($n['boxes'], 'box') === ['C']);

        $response = $this->post(route('member.record.help', $record->Id_Record), ['box' => 'sub_assy|C']);
        $this->assertSame('C', Record_List::where('Id_Record_List', $this->nextId($response))->value('Box'));
    }

    public function test_scan_page_flags_start_of_box_in_box_mode(): void
    {
        $record = $this->makeRecord();
        $record->update(['Scan_Order' => 'box']);
        $owner = $this->member(1001, 'N1001');
        Auth::guard('member')->setUser($owner);
        $parts = Record_List::where('Id_Record', $record->Id_Record)->orderBy('Sequence_No')->get();

        $this->get(route('member.record.scan-part', [$record->Id_Record, $parts[0]->Id_Record_List]))->assertViewHas('isBoxStart', true);
        $this->post(route('member.record.update-part', $parts[0]->Id_Record_List), ['Code_Rack' => $parts[0]->Code_Rack]);
        $this->get(route('member.record.scan-part', [$record->Id_Record, $parts[1]->Id_Record_List]))->assertViewHas('isBoxStart', false);
        $this->post(route('member.record.update-part', $parts[1]->Id_Record_List), ['Code_Rack' => $parts[1]->Code_Rack]);
        $this->get(route('member.record.scan-part', [$record->Id_Record, $parts[2]->Id_Record_List]))->assertViewHas('isBoxStart', true);
    }

    // Id part dari redirect ke scan-part/{record}/{recordList}
    private function nextId($response): int
    {
        return (int) basename(parse_url($response->headers->get('Location'), PHP_URL_PATH));
    }
}
