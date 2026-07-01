using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;
using System.Text.Json;

namespace Enxoval.Web.Models;

public class Desejo
{
    public int Id { get; set; }

    [Required, MaxLength(200)]
    public string Nome { get; set; } = "";

    public string? LinksJson { get; set; }

    [MaxLength(100)]
    public string? QuemAdicionou { get; set; }

    public DateTime DataAdicao { get; set; } = DateTime.UtcNow;
    public bool Comprado { get; set; }

    public int? CategoriaId { get; set; }
    public Categoria? Categoria { get; set; }

    [NotMapped]
    public List<LinkProduto> Links
    {
        get => string.IsNullOrEmpty(LinksJson) ? new() : JsonSerializer.Deserialize<List<LinkProduto>>(LinksJson) ?? new();
        set => LinksJson = JsonSerializer.Serialize(value);
    }
}
