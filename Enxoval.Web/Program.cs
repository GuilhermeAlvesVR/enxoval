using Microsoft.EntityFrameworkCore;
using Enxoval.Web.Data;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllersWithViews();

var databaseUrl = Environment.GetEnvironmentVariable("DATABASE_URL");
if (!string.IsNullOrEmpty(databaseUrl))
{
    var uri = new Uri(databaseUrl);
    var userInfo = uri.UserInfo.Split(':');
    try
    {
        var entry = System.Net.Dns.GetHostEntry(uri.Host, System.Net.Sockets.AddressFamily.InterNetwork);
        uri = new UriBuilder(uri) { Host = entry.AddressList[0].ToString() }.Uri;
    }
    catch { }
    var builder_ = new Npgsql.NpgsqlConnectionStringBuilder
    {
        Host = uri.Host,
        Database = uri.AbsolutePath.TrimStart('/'),
        Username = userInfo[0],
        Password = userInfo[1],
        SslMode = Npgsql.SslMode.Require
    };
    if (uri.Port > 0) builder_.Port = uri.Port;
    var connString = builder_.ConnectionString;

    builder.Services.AddDbContext<AppDbContext>(o =>
        o.UseNpgsql(connString));
}
else
{
    builder.Services.AddDbContext<AppDbContext>(o =>
        o.UseSqlite("Data Source=enxoval.db"));
}

var app = builder.Build();

using (var scope = app.Services.CreateScope())
{
    var db = scope.ServiceProvider.GetRequiredService<AppDbContext>();
    db.Database.EnsureCreated();
}

app.UseStaticFiles();
app.UseRouting();
app.MapControllerRoute("default", "{controller=Home}/{action=Index}/{id?}");

app.Run();
