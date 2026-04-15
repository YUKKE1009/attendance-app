<div class="actions">
    @if($attendance->status == '承認待ち')
    <form action="{{ route('admin.attendance.approve', $attendance->id) }}" method="POST">
        @csrf
        <button type="submit" class="approve-btn">承認する</button>
    </form>
    @else
    <p class="status-msg">*承認待ちのため修正はできません。</p>
    @endif
</div>