<li>{{ $child_coa->name }}</li>
<ul>
    @foreach ($child_coa->children as $childCategory)
        @include('admin.layouts.menu_child', ['child_coa' => $childCategory])
    @endforeach
</ul>