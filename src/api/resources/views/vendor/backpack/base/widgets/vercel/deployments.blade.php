
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_start')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <strong>{!! $widget['heading'] ?? 'Vercel' !!}</strong>
      <a class="btn btn-primary" href="{{ url('/admin/vercel/redeploy') }}" role="button">{{ trans('parabellumkoval::vercel.rebuild_frontend') }}</a>
    </div>
  </div>
  <div class="card-body">
    <table class="table table-responsive-sm">
      <thead>
        <tr>
          <th>{{ trans('parabellumkoval::vercel.build_url') }}</th>
          <th>{{ trans('parabellumkoval::vercel.build_date') }}</th>
          <th>{{ trans('parabellumkoval::vercel.git') }}</th>
          <th>{{ trans('parabellumkoval::vercel.status') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($widget['list']['deployments'] as $key => $item)
          <tr>
            <td>
              @if($key === 0)
                <div>
                  <a href="{{ config('parabellumkoval.vercel.frontend_url') }}" class="text-primary" target="_blank">
                    <i class="la la-external-link"></i> <strong>{{ config('parabellumkoval.vercel.frontend_url') }}</strong>
                  </a>
                </div>
              @else
              <div>
                <a href="https://{{ $item['url'] }}"target="_blank"><i class="la la-external-link"></i> https://{{ $item['url'] }}</a>
              </div>
              @endif
            </td>
            <td>{{ \Carbon\Carbon::createFromTimestampMs($item['createdAt']) }}</td>
            <td>
              @if(isset($item['meta']['githubCommitRef']))
              <div>
                <i class="la la-github la-lg mr-1"></i>&nbsp;
                {{ $item['meta']['githubCommitRef'] }}
              </div>
              @endif
              @if(isset($item['meta']['githubCommitMessage']) && isset($item['meta']['githubCommitSha']))
              <div>
                <i class="la la-minus la-sm"></i>&nbsp;
                <span class="mr-3">{{ substr($item['meta']['githubCommitSha'], 0, 7) }}</span>
                <i class="la la-code la-sm"></i>&nbsp;
                <span >{{ $item['meta']['githubCommitMessage'] }}</span>
              </div>
              @endif
            </td>
            <td>
              <span class="badge badge-{{ \Backpack\Vercel\app\Models\Vercel::status($item['state']) }}">{{ $item['state'] }}</span>
              @if($key === 0)
                <div class="text-muted">{{ trans('parabellumkoval::vercel.current') }}</div>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')

@push('after_styles')
<style>
</style>
@endpush