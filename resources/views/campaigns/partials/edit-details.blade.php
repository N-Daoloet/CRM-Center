@if($errors->any())
    <div class="alert" style="background:#fef2f2; color:#991b1b; margin-top:16px;">
        <ul style="margin:0; padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-grid">
    <label class="full-span">ชื่อแคมเปญ
        <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required>
    </label>

    <label>ประเภทแคมเปญ
        <input type="text" value="{{ $campaignTypeLabel }}" readonly>
    </label>

    <label>วันที่เริ่มต้น
        <input type="date" name="start_date" value="{{ old('start_date', substr((string) $campaign->start_date, 0, 10)) }}" required>
    </label>

    <label>วันที่สิ้นสุด
        <input type="date" name="end_date" value="{{ old('end_date', substr((string) $campaign->end_date, 0, 10)) }}" required>
    </label>

    <label>สถานะ
        <select name="status">
            @foreach(['active' => 'กำลังใช้งาน', 'planned' => 'วางแผน', 'completed' => 'เสร็จสิ้น'] as $value => $label)
                <option value="{{ $value }}" {{ old('status', $campaign->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
            @if($campaign->status && !in_array($campaign->status, ['active', 'planned', 'completed'], true))
                <option value="{{ $campaign->status }}" selected>{{ $campaign->status }}</option>
            @endif
        </select>
    </label>

    <label class="full-span">รายละเอียดแคมเปญ
        <textarea rows="4" name="description">{{ old('description', $campaign->description) }}</textarea>
    </label>
</div>