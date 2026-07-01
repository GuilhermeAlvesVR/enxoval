using Microsoft.EntityFrameworkCore;
using Enxoval.Web.Models;

namespace Enxoval.Web.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }
    public DbSet<Desejo> Desejos { get; set; }
}
