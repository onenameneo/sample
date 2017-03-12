@if($user->id !== Auth::user()->id)

    <div id="follow_form">
        @if(Auth::user()->isFollowing($user->id))
            <form action="{{ route('followers.destroy', $user->id) }}" method="post">
                <button type="submit" class="btn btn-sm">取消关注</button>
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
            </form>
        @else
            <form action="{{ route('followers.store', $user->id) }}" method="post">
                <button type="submit" class="btn btn-sm">关注</button>
                {{ csrf_field() }}
            </form>
        @endif
    </div>

@endif