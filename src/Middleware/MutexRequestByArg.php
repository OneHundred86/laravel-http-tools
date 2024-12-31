<?php

namespace Oh86\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Oh86\Http\Exceptions\ErrorCodeException;

class MutexRequestByArg
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $argName
     * @param  int  $waitSeconds
     * @param  int  $lockSeconds
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $argName, int $waitSeconds = 10, int $lockSeconds = 300)
    {
        $val = $request->$argName;
        $lockName = "mutexRequest:$argName:$val";
        $lock = Cache::lock($lockName, $lockSeconds);

        $isLocked = false;
        try {
            $isLocked = (bool) $lock->block($waitSeconds);
            return $next($request);
        } catch (LockTimeoutException $exception) {
            throw new ErrorCodeException(502, "系统繁忙，请稍后再试", null, 502);
        } finally {
            $isLocked && $lock->release();
        }
    }
}
