<ul>
    @foreach ($coas as $coa)
        <li>{{ $coa->name }}</li>
        <ul>
            @foreach ($coa->children as $coachild)
                @include('admin.layouts.menu_child', ['child_coa' => $coachild])
            @endforeach
        </ul>
    @endforeach
</ul>