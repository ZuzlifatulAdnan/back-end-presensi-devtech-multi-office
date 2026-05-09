Error
PHP 8.3.22
12.31.1
Call to a member function format() on string

Expand vendor frames
App
 \ 
Filament
 \ 
Resources
 \ 
Attendances
 \ 
Tables
 \ 
AttendancesTable
 
: 166
App\Filament\Resources\Attendances\Tables\{closure}
62 vendor frames
public
 / 
index
.php
 
: 17
require_once
1 vendor frame
app
 / 
Filament
 / 
Resources
 / 
Attendances
 / 
Tables
 / 
AttendancesTable
.php
 
: 166































                        $attendances = $query->with(['user', 'shift'])->get();



                        $csv = "User,Date,Check In,Check Out,Status,Total Hours,Shift\n";

                        foreach ($attendances as $attendance) {

                            $totalHours = '-';

                            if ($attendance->time_out) {

                                $checkIn = \Carbon\Carbon::parse($attendance->time_in);

                                $checkOut = \Carbon\Carbon::parse($attendance->time_out);

                                $duration = $checkIn->diff($checkOut);

                                $totalHours = sprintf('%d:%02d', $duration->h, $duration->i);

                            }



                            $csv .= sprintf(

                                '"%s","%s","%s","%s","%s","%s","%s"'."\n",

                                $attendance->user->name,

                                $attendance->date->format('d M Y'),

                                $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-',

                                $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-',

                                ucfirst(str_replace('_', ' ', $attendance->status)),

                                $totalHours,

                                $attendance->shift->name ?? 'No Shift'

                            );

                        }



                        return response()->streamDownload(function () use ($csv) {

                            echo $csv;

                        }, 'attendances-'.now()->format('Y-m-d').'.csv');

                    }),

                BulkActionGroup::make([

                    DeleteBulkAction::make(),
App
Routing
Request
Browser
Headers
Body
Livewire
app.filament.resources.attendances.pages.list-attendances
Context
User
Git
Versions
App
Routing
Controller
Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate

Route name
livewire.update

Middleware
web

Request
http://127.0.0.1:8000/admin/attendances
GET
curl "http://127.0.0.1:8000/admin/attendances" \
   -X GET \
   -H 'host: 127.0.0.1:8000' \
   -H 'connection: keep-alive' \
   -H 'content-length: 3603' \
   -H 'sec-ch-ua-platform: "macOS"' \
   -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36' \
   -H 'sec-ch-ua: "Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"' \
   -H 'content-type: application/json' \
   -H 'sec-ch-ua-mobile: ?0' \
   -H 'accept: */*' \
   -H 'origin: http://127.0.0.1:8000' \
   -H 'sec-fetch-site: same-origin' \
   -H 'sec-fetch-mode: cors' \
   -H 'sec-fetch-dest: empty' \
   -H 'referer: http://127.0.0.1:8000/admin/attendances' \
   -H 'accept-encoding: gzip, deflate, br, zstd' \
   -H 'accept-language: en-US,en;q=0.9,id;q=0.8' \
   -H 'cookie: <CENSORED>' \
   -F '_token=LZy0WgqCTERTzlehCIFRBjJxwhMNG9sapUNH8pZJ' -F 'components=[object Object]'


Browser
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36

Headers
host
127.0.0.1:8000

connection
keep-alive

content-length
3603

sec-ch-ua-platform
"macOS"

user-agent
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36

sec-ch-ua
"Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"

content-type
application/json

sec-ch-ua-mobile
?0

accept
*/*

origin
http://127.0.0.1:8000

sec-fetch-site
same-origin
