using Microsoft.EntityFrameworkCore;
using Enxoval.Web.Data;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllersWithViews();

var databaseUrl = Environment.GetEnvironmentVariable("DATABASE_URL");
if (!string.IsNullOrEmpty(databaseUrl))
{
    var uri = new Uri(databaseUrl);
    var userInfo = uri.UserInfo.Split(':');
    var addresses = System.Net.Dns.GetHostAddresses(uri.Host);
    var ipv4 = addresses.FirstOrDefault(a => a.AddressFamily == System.Net.Sockets.AddressFamily.InterNetwork);
    var connString = new Npgsql.NpgsqlConnectionStringBuilder
    {
        Host = ipv4?.ToString() ?? uri.Host,
        Port = uri.Port,
        Database = uri.AbsolutePath.TrimStart('/'),
        Username = userInfo[0],
        Password = userInfo[1],
        SslMode = Npgsql.SslMode.Require
    }.ConnectionString;

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
