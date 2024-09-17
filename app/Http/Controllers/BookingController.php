<?php
namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterface;


class BookingController extends Controller
{
    public function index()
    {
        // Set locale to Arabic for Carbon
        Carbon::setLocale('ar');

        // Fetch the latest table's creation date (if it exists) to calculate the week
        $latestTable = Table::orderBy('created_at', 'desc')->first();
        
        // Start week from the latest table creation or from current week if no tables exist
        $currentWeekStart = $latestTable 
            ? (new Carbon($latestTable->created_at))->startOfWeek(Carbon::SUNDAY)
            : Carbon::now()->startOfWeek(Carbon::SUNDAY);
        
        // Generate days for the week (Sunday to Thursday)
        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $days[] = [
                'name' => $currentWeekStart->copy()->addDays($i)->translatedFormat('l'), // Day name in Arabic
                'date' => $currentWeekStart->copy()->addDays($i)->format('Y-m-d') // Date format
            ];
        }
    
        $periods = range(1, 8); // Periods from 1 to 8
        $tables = $this->getTables();

        // Group bookings by table_id, day (date), and period
        $bookings = Booking::all()->groupBy('table_id')->map(function ($tableBookings) {
            return $tableBookings->groupBy('day')->map(function ($dayBookings) {
                return $dayBookings->keyBy('period');
            });
        });
    
        return view('welcome', compact('days', 'periods', 'tables', 'bookings'));
    }
    
    public function book(Request $request)
    {
        // Validate booking request
        $request->validate([
            'day' => 'required|date',
            'period' => 'required|integer|min:1|max:8',
            'booker_name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'table_id' => 'required|exists:tables,id',
            'password' => 'required|string',
        ]);

        // Verify password before booking
        if ($request->input('password') !== 'ahmed5') {
            return redirect()->route('home')->with('error', 'كلمة المرور غير صحيحة!');
        }

        // Check if the table is already booked for the same day and period
        $existingBooking = Booking::where('table_id', $request->input('table_id'))
            ->where('day', $request->input('day'))
            ->where('period', $request->input('period'))
            ->first();

        if ($existingBooking) {
            return redirect()->route('home')->with('error', 'هذه الفترة محجوزة بالفعل في هذا الجدول!');
        }

        // Create new booking
        Booking::create([
            'day' => $request->input('day'),
            'period' => $request->input('period'),
            'booker_name' => $request->input('booker_name'),
            'subject' => $request->input('subject'),
            'table_id' => $request->input('table_id'),
        ]);

        return redirect()->route('home')->with('success', 'تم الحجز بنجاح!');
    }

    public function createNewTable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);
    
        if ($request->input('password') !== 'ahmed5') {
            return redirect()->route('home')->with('error', 'كلمة المرور غير صحيحة!');
        }
    
        $tables = $this->getTables();
        
        if (count($tables) >= 5) {
            // Remove the oldest table and related bookings
            $oldestTable = $tables->last();
            Booking::where('table_id', $oldestTable->id)->delete();
            $oldestTable->delete();
        }
    
        // Calculate start of the next week
        $nextWeekStart = $tables->isNotEmpty()
            ? (new Carbon($tables->first()->created_at))->startOfWeek(Carbon::SUNDAY)->addDay()->endOfWeek()
            : Carbon::now()->startOfWeek(Carbon::SUNDAY)->addWeek();
        // Create dates for the new week (Sunday to Thursday)
        $newWeekDates = [];
        for ($i = 0; $i < 5; $i++) {
            $newWeekDates[] = $nextWeekStart->copy()->addDays($i)->format('Y-m-d');
        }
        $latestTable = Table::orderBy('created_at', 'desc')->first();
        $nextWeekStart = $latestTable;


        // Create new table
        Table::create([
            'name' => 'Table ' . (count($tables) + 1),
        ]);
        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $days[] = [
                'name' => $nextWeekStart->copy()->addDays($i)->translatedFormat('l'), // Day name in Arabic
                'date' => $nextWeekStart->copy()->addDays($i)->format('Y-m-d') // Date format
            ];
        }
    
        $periods = range(1, 8); // Periods from 1 to 8
        $tables = $this->getTables();

        // Group bookings by table_id, day (date), and period
        $bookings = Booking::all()->groupBy('table_id')->map(function ($tableBookings) {
            return $tableBookings->groupBy('day')->map(function ($dayBookings) {
                return $dayBookings->keyBy('period');
            });
        });
        return view('welcome', compact('days', 'periods', 'tables', 'bookings'));    }

    private function getTables()
    {
        return Table::latest()->take(5)->get();
    }
}
