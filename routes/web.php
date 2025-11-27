<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteReportController;
use App\Http\Controllers\SiteMapController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    //Dashboard Punya
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/trend-data', [DashboardController::class, 'getTrendData'])->name('dashboard.trend-data');
    Route::get('/dashboard/city-data', [DashboardController::class, 'getCityData'])->name('dashboard.city-data');

    //Repair Reports Punya
    Route::get('/reports/search-suggestions', [ReportController::class, 'searchSuggestions'])->name('reports.search-suggestions');
    Route::resource('reports', ReportController::class);

    //Site Reports Punya
    Route::resource('site-reports', SiteReportController::class);
    Route::post('site-reports/{siteReport}/close', [SiteReportController::class, 'close'])->name('site-reports.close');
    Route::post('site-reports/{siteReport}/reopen', [SiteReportController::class, 'reopen'])->name('site-reports.reopen');
    Route::post('site-reports/bulk-close', [SiteReportController::class, 'bulkClose'])->name('site-reports.bulk-close');
    Route::delete('site-reports/bulk-delete', [SiteReportController::class, 'bulkDelete'])->name('site-reports.bulk-delete');

    //Sites Punya
    Route::get('/sites/search', [SiteController::class, 'searchSuggestions'])->name('sites.search');
    Route::resource('sites', SiteController::class);

    //Repair Map Punya
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::get('/map/data', [MapController::class, 'data'])->name('map.data');
    Route::get('/map/city-polygons', [MapController::class, 'getCityPolygons'])->name('map.city-polygons');
    Route::get('/map/reports-by-city', [MapController::class, 'getReportsByCity'])->name('map.reports-by-city');
    Route::get('/map/clear-cache', [MapController::class, 'clearPolygonCache'])->name('map.clear-cache');
    Route::get('/map/kml/{filename}', [MapController::class, 'getKmlFile'])->name('map.kml');

    //Site Map Punya
    Route::get('/site-map', [SiteMapController::class, 'index'])->name('site-map.index');
    Route::get('/site-map/data', [SiteMapController::class, 'data'])->name('site-map.data');
    Route::get('/site-map/site/{siteId}/reports', [SiteMapController::class, 'getSiteReports'])->name('site-map.site-reports');
    Route::get('/site-map/site/{siteId}/all-reports', [SiteMapController::class, 'getAllSiteReports'])->name('site-map.all-reports');
    Route::get('/site-map/city-polygons', [SiteMapController::class, 'getCityPolygons'])->name('site-map.city-polygons');
    Route::get('/site-map/sites-by-city', [SiteMapController::class, 'getSitesByCity'])->name('site-map.sites-by-city');
    Route::get('/site-map/statistics', [SiteMapController::class, 'getStatistics'])->name('site-map.statistics');
    Route::get('/site-map/kml/{filename}', [SiteMapController::class, 'getKmlFile'])->name('site-map.kml');



});
