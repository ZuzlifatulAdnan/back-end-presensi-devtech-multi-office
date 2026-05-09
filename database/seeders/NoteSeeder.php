<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = \App\Models\User::where('email', 'admin@admin.com')->first();

        if (! $adminUser) {
            return;
        }

        $notes = [
            [
                'user_id' => $adminUser->id,
                'title' => 'Welcome to the System',
                'note' => 'This is a welcome note for all new users. Please read the handbook and follow company policies.',
            ],
            [
                'user_id' => $adminUser->id,
                'title' => 'Meeting Schedule',
                'note' => 'Monthly team meeting is scheduled for every first Monday of the month at 10:00 AM.',
            ],
            [
                'user_id' => $adminUser->id,
                'title' => 'Holiday Notice',
                'note' => 'Please note that the office will be closed on national holidays. Check the holiday calendar for details.',
            ],
        ];

        foreach ($notes as $note) {
            \App\Models\Note::updateOrCreate(
                [
                    'user_id' => $note['user_id'],
                    'title' => $note['title'],
                ],
                $note
            );
        }
    }
}
