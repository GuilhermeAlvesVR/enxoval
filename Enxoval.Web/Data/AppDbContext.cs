using Microsoft.EntityFrameworkCore;
using Enxoval.Web.Models;

namespace Enxoval.Web.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }
    public DbSet<Desejo> Desejos { get; set; }
    public DbSet<Categoria> Categorias { get; set; }

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder.Entity<Categoria>()
            .HasMany(c => c.Desejos)
            .WithOne(d => d.Categoria)
            .HasForeignKey(d => d.CategoriaId)
            .OnDelete(DeleteBehavior.SetNull);
    }
}
