<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام حجز المدرسة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table th, .table td.green {
            background-color: #208938; /* Light green */
        }
    </style>
    <h1 style="text-align: center;">حجز قاعة مصادر التعلم</h1>
    <h2 style="text-align: center;"> الرجاء الضغط على كلمة حجز واكتب الاسم والمادة و كلمة المرور</h2>
</head>
<body>
    <div class="container mt-5">
        <h1 style="text-align: center;">ALNHDA-SCHOOL</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Button to add a new table -->
        <div class="mb-4" style="text-align: left;">
            <button id="addNewTableButton" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#confirmNewTableModal">إضافة جدول جديد</button>
        </div>

        <!-- Current Table Display -->
        @foreach ($tables as $table)
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>الحصة</th>
                    @foreach ($days as $day)
                        <th>{{ $day['name'] }}<br>{{ $day['date'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($period = 1; $period <= 8; $period++)
                    <tr>
                        <th>الحصة {{ $period }}</th>
                        @foreach ($days as $day)
                            <td>
                                @php
                                    $isBooked = isset($bookings[$table->id][$day['date']][$period]);
                                @endphp

                                @if ($isBooked)
                                    <button class="btn btn-success disabled">
                                        محجوز ({{ $bookings[$table->id][$day['date']][$period]->booker_name }})
                                    </button>
                                @else
                                    <button class="btn btn-primary" 
                                            data-day="{{ $day['date'] }}" 
                                            data-period="{{ $period }}" 
                                            data-table-id="{{ $table->id }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bookingModal">احجز
                                    </button>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
        @endforeach

    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="bookingForm" action="{{ route('book') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingModalLabel">حجز موعد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="table_id" id="modalTableId">
                        <input type="hidden" name="day" id="modalDay">
                        <input type="hidden" name="period" id="modalPeriod">
                        
                        <div class="mb-3">
                            <label for="booker_name" class="form-label">اسم الحاجز</label>
                            <input type="text" name="booker_name" class="form-control" id="booker_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">المادة</label>
                            <input type="text" name="subject" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">حجز</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Table Modal -->
    <div class="modal fade" id="confirmNewTableModal" tabindex="-1" aria-labelledby="confirmNewTableModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('create.new.table') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmNewTableModalLabel">إضافة جدول جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">إضافة جدول</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle table booking modal data
        const bookingModal = document.getElementById('bookingModal');
        bookingModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const day = button.getAttribute('data-day');
            const period = button.getAttribute('data-period');
            const tableId = button.getAttribute('data-table-id');
            
            const modalDayInput = bookingModal.querySelector('#modalDay');
            const modalPeriodInput = bookingModal.querySelector('#modalPeriod');
            const modalTableIdInput = bookingModal.querySelector('#modalTableId');
            
            modalDayInput.value = day;
            modalPeriodInput.value = period;
            modalTableIdInput.value = tableId;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
